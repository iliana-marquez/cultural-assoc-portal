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
     * For stage='profile', any existing profile image for this entity is
     * deleted first (both Cloudinary file and DB record) — profile is a
     * single-image slot, not a gallery. Without this, old profile images
     * accumulate silently: getFirstForEntity() always shows the oldest
     * one, while newer uploads stay orphaned in the DB and Cloudinary,
     * confusing the editor about which upload is actually "the" photo.
     *
     * POST params:
     *   entity_type  string  'event' | 'participant' | 'team' | 'organisation' | 'venue'
     *   entity_id    int
     *   stage        string  'promo' | 'gallery' | 'profile'
     *   caption      string  optional
     *   credit       string  optional — photographer credit
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
        $credit     = trim($_POST['credit']  ?? '');
        $subfolder  = trim($_POST['subfolder'] ?? $entityType);
        $requestedPublicId = trim($_POST['public_id'] ?? '');

        if (!$entityType || !$entityId) {
            $this->jsonError('entity_type and entity_id are required');
            return;
        }

        try {
            // Profile is a single-image slot — replace, never accumulate
            if ($stage === 'profile') {
                $existing = $this->mediaModel->getFirstForEntity($entityType, $entityId, 'profile');
                if ($existing) {
                    $existingPublicId = CloudinaryService::extractPublicId($existing->media_url);
                    $this->mediaModel->unlinkFromEntity($existing->id, $entityType, $entityId);
                    $remaining = $this->mediaModel->getById($existing->id);
                    if (!$remaining && $existingPublicId) {
                        CloudinaryService::delete($existingPublicId);
                    }
                }
            }

            // Use the caller-provided public_id (slug-based naming) when given,
            // otherwise fall back to the generic entity-id-based pattern.
            $publicId = CloudinaryService::generatePublicId(
                $requestedPublicId !== ''
                    ? $requestedPublicId
                    : $entityType . '-' . $entityId . '-' . time()
            );

            // Upload to Cloudinary
            $result = CloudinaryService::upload(
                $_FILES['image'],
                $subfolder,
                $publicId
            );

            // Insert media + link to entity via pivot
            $mediaId = $this->mediaModel->addForEntity($entityType, $entityId, [
                'media_url'   => $result['secure_url'],
                'caption'     => $caption ?: null,
                'credit'      => $credit  ?: null,
                'stage'       => $stage,
                'order_index' => 0,
            ]);

            $this->jsonSuccess([
                'id'        => $mediaId,
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
     * public_id is recovered from the stored media_url via
     * CloudinaryService::extractPublicId() — no public_id column or
     * client-supplied value needed, and works retroactively for any
     * media uploaded before this fix existed. Same approach used for
     * section images and organisation logos.
     *
     * POST params:
     *   entity_type  string
     *   entity_id    int
     */
    public function delete(array $params = []): void
    {
        $this->requireLogin();

        $mediaId    = (int) ($params['id'] ?? 0);
        $entityType = trim($_POST['entity_type'] ?? '');
        $entityId   = (int) ($_POST['entity_id'] ?? 0);

        if (!$mediaId || !$entityType || !$entityId) {
            $this->jsonError('media_id, entity_type and entity_id are required');
            return;
        }

        // Fetch BEFORE unlinking — the row won't exist afterward if
        // this was the last link, and we need media_url to recover
        // the public_id for Cloudinary deletion.
        $media = $this->mediaModel->getById($mediaId);

        // Unlink from entity — deletes media record if no more links
        $this->mediaModel->unlinkFromEntity($mediaId, $entityType, $entityId);

        // Delete from Cloudinary only if the media record was actually
        // removed (no more entities reference it)
        if ($media) {
            $remaining = $this->mediaModel->getById($mediaId);
            if (!$remaining) {
                $publicId = CloudinaryService::extractPublicId($media->media_url);
                if ($publicId) {
                    CloudinaryService::delete($publicId);
                }
            }
        }

        $this->jsonSuccess();
    }

    /**
     * POST /media/{id}/meta
     * Update caption and/or credit for a single media item.
     *
     * POST params:
     *   caption  string  optional
     *   credit   string  optional
     */
    public function updateMeta(array $params = []): void
    {
        $this->requireLogin();

        $mediaId = (int) ($params['id'] ?? 0);

        if (!$mediaId) {
            $this->jsonError('media_id required');
            return;
        }

        $data = [];
        if (isset($_POST['caption'])) {
            $data['caption'] = trim($_POST['caption']) ?: null;
        }
        if (isset($_POST['credit'])) {
            $data['credit'] = trim($_POST['credit']) ?: null;
        }

        if (empty($data)) {
            $this->jsonError('No data provided');
            return;
        }

        $success = $this->mediaModel->update($mediaId, $data);
        $success ? $this->jsonSuccess() : $this->jsonError('Failed to update');
    }

    /**
     * POST /media/batch-meta
     * Update caption and/or credit for multiple media items at once.
     *
     * POST params:
     *   ids[]    array   media ids
     *   caption  string  optional
     *   credit   string  optional
     */
    public function batchMeta(array $params = []): void
    {
        $this->requireLogin();

        $ids = $_POST['ids'] ?? [];

        if (empty($ids)) {
            $this->jsonError('No ids provided');
            return;
        }

        $data = [];
        if (array_key_exists('caption', $_POST)) {
            $data['caption'] = trim($_POST['caption']) ?: null;
        }
        if (array_key_exists('credit', $_POST)) {
            $data['credit'] = trim($_POST['credit']) ?: null;
        }

        if (empty($data)) {
            $this->jsonError('No data provided');
            return;
        }

        foreach ($ids as $id) {
            $this->mediaModel->update((int) $id, $data);
        }

        $this->jsonSuccess();
    }

    /**
     * POST /media/upload-section
     * Upload image for a free section — stores URL in section JSON via PageController.
     * Returns URL for JS to update DOM immediately.
     */
    public function uploadSection(array $params = []): void
    {
        $this->requireLogin();

        if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonError('No file uploaded');
            return;
        }

        $sectionId = (int) ($_POST['entity_id'] ?? 0);
        $field     = trim($_POST['field'] ?? 'image'); // 'image' or 'bg_image'

        if (!$sectionId) {
            $this->jsonError('Section ID required');
            return;
        }

        try {
            $publicId = CloudinaryService::generatePublicId('section-' . $sectionId . '-' . $field . '-' . time());
            $result   = CloudinaryService::upload($_FILES['image'], 'pages', $publicId);

            // Save URL directly into section JSON via PagesModel
            require_once __DIR__ . '/../Models/PagesModel.php';
            $pagesModel = new PagesModel();
            $existing   = $pagesModel->getById($sectionId);
            $current    = json_decode($existing->content ?? '{}', true) ?: [];
            $current[$field] = $result['secure_url'];
            $pagesModel->updateContent($sectionId, $current);

            $this->jsonSuccess(['url' => $result['secure_url']]);
        } catch (RuntimeException $e) {
            $this->jsonError($e->getMessage());
        }
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
