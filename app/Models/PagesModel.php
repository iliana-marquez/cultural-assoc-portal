<?php

/**
 * PagesModel
 *
 * Fetches page sections (pages table) from the pages table.
 * Each row is a section belonging to a page.
 * Content stored as JSON — decoded to object on fetch.
 */

class PagesModel extends BaseModel
{
    private string $table = 'pages';

    /**
     * Get all sections for a given page, ordered by order_index.
     * Decodes JSON content into object for use in section components.
     *
     * @param string $pageKey  Page identifier e.g. 'home', 'ueber-uns'
     * @return array           Array of section objects
     */
    public function getForPage(string $pageKey): array
    {
        $rows = $this->fetchAll(
            "SELECT * FROM {$this->table}
             WHERE page_key = ?
             ORDER BY order_index ASC",
            [$pageKey]
        );

        // Decode JSON content into object for each section
        return array_map(function ($row) {
            $content = json_decode($row->content ?? '{}');
            // Merge page metadata with content fields
            $content->type        = $row->type_key;
            $content->id          = $row->id;
            $content->page_key    = $row->page_key;
            $content->section_key = $row->section_key;
            $content->order_index = $row->order_index;
            return $content;
        }, $rows);
    }

    /**
     * Update section content JSON.
     * Called from edit mode when editor saves changes.
     *
     * @param int    $id      Section row ID
     * @param array  $content Content array to encode as JSON
     * @return bool
     */
    public function updateContent(int $id, array $content): bool
    {
        return $this->execute(
            "UPDATE {$this->table} SET content = ? WHERE id = ?",
            [json_encode($content, JSON_UNESCAPED_UNICODE), $id]
        );
    }

    /**
     * Add a new section to a page.
     *
     * @param string $pageKey    Page identifier
     * @param string $sectionKey Section identifier
     * @param int    $orderIndex Position on page
     * @param array  $content    Section content
     * @return bool
     */
    public function addSection(string $pageKey, string $sectionKey, int $orderIndex, array $content): bool
    {
        return $this->execute(
            "INSERT INTO {$this->table} (page_key, section_key, order_index, content)
             VALUES (?, ?, ?, ?)",
            [$pageKey, $sectionKey, $orderIndex, json_encode($content, JSON_UNESCAPED_UNICODE)]
        );
    }

    /**
     * Delete a section.
     *
     * @param int $id Section row ID
     * @return bool
     */
    public function deleteSection(int $id): bool
    {
        return $this->execute(
            "DELETE FROM {$this->table} WHERE id = ?",
            [$id]
        );
    }

    /**
     * Update section order index.
     * Used by reorderSections() in PageController.
     *
     * @param int $id         Section row ID
     * @param int $orderIndex New order index
     * @return bool
     */
    public function updateOrder(int $id, int $orderIndex): bool
    {
        return $this->execute(
            "UPDATE {$this->table} SET order_index = ? WHERE id = ?",
            [$orderIndex, $id]
        );
    }
}
