<?php
namespace App\Http\Controllers;

use App\Models\Vote;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Mathrix\Lumen\Http\Controllers\BaseController;
use Mathrix\Lumen\Models\BaseModel;

class VoteController extends BaseController
{
    /**
     * @param string $type
     * @param int $id
     *
     * @return JsonResponse
     */
    public function up(string $type = 'comment', int $id)
    {
        // Ensure that the type is morphed
        /** @var BaseModel $class */
        if ($class = Relation::getMorphedModel($type) == null) {
            return $this->http400InvalidRequest();
        }

        // Ensure that the morphed exists
        $model = $class::where('id', '=', $id)->firstOrFail();
        $model->increment('vote_score');

        $vote = new Vote();
        $vote->fill([]);

        if (!$vote->save()) {
            return $vote->handleResponse($this->http422Save());
        }

        return $this->http200Success($vote);
    }
}