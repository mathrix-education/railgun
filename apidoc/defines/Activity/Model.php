<?php
/**
 * @apiDefine ActivityModel
 * @apiSuccess {int} id The activity id
 * @apiSuccess {string} name The activity name
 * @apiSuccess {string} slug The activity slug
 * @apiSUccess {string=video,course,test,exercise,summary,flashcard} type The activity type
 * @apiSuccess {string} description The activity description
 * @apiSuccess {bool} visible The activity visibility
 * @apiSuccess {bool} favorite (Logged only) If the activity is marked as favorite
 * @apiSuccess {bool} to_revise (Logged only) If the activity is marked as to revise
 * @apiSuccess {bool} completed (Logged only) If the activity is marked as completed
 */
