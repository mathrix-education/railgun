<?php
/**
 * @apiDefine VoteArray
 * @apiSuccess {Vote[]} votes Array of votes
 * @apiSuccess {int} votes.id The vote id
 * @apiSuccess {int} votes.value The vote value
 * @apiSuccess {int} votes.user_id The vote user_id
 * @apiSuccess {string} votes.created_at The vote created_at
 * @apiSuccess {string} votes.updated_at The vote updated_at
 * @apiSuccess {int} votes.votable_id The vote votable_id
 * @apiSuccess {string} votes.votable_type The vote votable_type
 */
