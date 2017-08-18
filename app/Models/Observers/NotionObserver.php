<?php
namespace App\Models\Observers;

use App\Models\Notion;
use Mathrix\Lumen\Observers\BaseObserver;

/**
 * Class NotionObserver
 *
 * @author Mathrix Team
 * @package App\Models\Observers
 */
class NotionObserver extends BaseObserver
{
    /**
     * @param Notion $notion
     *
     * @return bool
     */
    public function saving($notion)
    {
        // Ensure the name is present
        if (!$notion->validate(['name' => 'required|max:255'])) {
            return false;
        }

        // Sluggify if needed
        if (
            empty($notion->slug) ||
            ($notion->isDirty('name') && !$notion->isDirty('slug'))
        ) {
            $notion->slug = str_slug($notion->name);
        }

        return parent::saving($notion);
    }
}