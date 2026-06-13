<?php

/**
 * MediaController
 *
 * Handles media uploads via Cloudinary and media CRUD via MediaModel.
 * All routes require login — edit mode only.
 *
 * POST /media/upload          → upload file to Cloudinary → link to entity
 * POST /media/{id}/delete     → unlink from entity → delete from Cloudinary if orphaned
 * POST /media/reorder         → update order_index
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/MediaModel.php';
require_once __DIR__ . '/../../core/CloudinaryService.php';

class MediaController extends BaseController
{
    private MediaModel $mediaModel;

    public function __construct()
    {
        parent::__construct();
        $this->mediaModel = new MediaModel();
    }

    /**
     * POST /media/upload
     * Upload file to Cloudinary → insert into media → link to entity via pivot.
     *
     * POST params:
     *   entity_type  string  'event' | 'participant' | 'team' | 'organisation' | 'venue'
     *   entity_id    int
     *   stage        string  'promo' | 'gallery'
     *   caption      string  optional
     *   subfolder    string  optional — defaults to entity_type
     */
    public function upload(array $params = []): void
    {
        $this->requireLogin();

        if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonError('No file uploaded or upload error');
            return;
        }

        $entityType = trim($_POST['entity_type'] ?? '');
        $entityId   = (int) ($_POST['entity_id'] ?? 0);
        $stage      = trim($_POST['stage'] ?? 'promo');
        $caption    = trim($_POST['caption'] ?? '');
        $subfolder  = trim($_POST['subfolder'] ?? $entityType);

        if (!$entityType || !$entityId) {
            $this->jsonError('entity_type and entity_id are required');
            return;
        }

        try {
            // Upload to Cloudinary
            $result = CloudinaryService::upload(
                $_FILES['image'],
                $subfolder
            );

            // Insert media + link to entity via pivot
            $this->mediaModel->addForEntity($entityType, $entityId, [
                'media_url'     => $result['secure_url'],
                'resource_type' => $result['resource_type'],
                'caption'       => $caption ?: null,
                'stage'         => $stage,
                'order_index'   => 0,
            ]);

            $this->jsonSuccess([
                'media_url' => $result['secure_url'],
                'public_id' => $result['public_id'],
            ]);
        } catch (RuntimeException $e) {
            $this->jsonError($e->getMessage());
        }
    }

    /**
     * POST /media/{id}/delete
     * Unlink media from entity → delete from Cloudinary if no more links.
     *
     * POST params:
     *   entity_type  string
     *   entity_id    int
     *   public_id    string  Cloudinary public_id (optional — for Cloudinary deletion)
     */
    public function delete(array $params = []): void
    {
        $this->requireLogin();

        $mediaId    = (int) ($params['id'] ?? 0);
        $entityType = trim($_POST['entity_type'] ?? '');
        $entityId   = (int) ($_POST['entity_id'] ?? 0);
        $publicId   = trim($_POST['public_id'] ?? '');

        if (!$mediaId || !$entityType || !$entityId) {
            $this->jsonError('media_id, entity_type and entity_id are required');
            return;
        }

        // Unlink from entity — deletes media record if no more links
        $this->mediaModel->unlinkFromEntity($mediaId, $entityType, $entityId);

        // Delete from Cloudinary if public_id provided and media record deleted
        if ($publicId) {
            $remaining = $this->mediaModel->getById($mediaId);
            if (!$remaining) {
                CloudinaryService::delete($publicId);
            }
        }

        $this->jsonSuccess();
    }

    /**
     * POST /media/reorder
     * Update order_index for drag-and-drop reordering.
     *
     * POST params:
     *   order  JSON array  [{"id": 1, "order_index": 0}, ...]
     */
    public function reorder(array $params = []): void
    {
        $this->requireLogin();

        $order = json_decode($_POST['order'] ?? '[]', true);

        if (empty($order)) {
            $this->jsonError('No order data provided');
            return;
        }

        foreach ($order as $item) {
            $this->mediaModel->updateOrder(
                (int) $item['id'],
                (int) $item['order_index']
            );
        }

        $this->jsonSuccess();
    }
}
