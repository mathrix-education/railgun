<?php
/**
 * @apiDefine ActivityArray
 * @apiSuccess {Activity[]} activity Array of activities
 * @apiSuccess {int} activities.id The activity id
 * @apiSuccess {string} activities.name The activity name
 * @apiSuccess {string} activities.slug The activity slug
 * @apiSUccess {string=video,course,test,exercise,summary,flashcard} activities.type The activity type
 * @apiSuccess {string} activities.description The activity description
 * @apiSuccess {bool} activities.visible The activity visibility
 * @apiSuccess {bool} activities.favorite (Logged only) If the activity is marked as favorite
 * @apiSuccess {bool} activities.to_revise (Logged only) If the activity is marked as to revise
 * @apiSuccess {bool} activities.completed (Logged only) If the activity is marked as completed
 */
