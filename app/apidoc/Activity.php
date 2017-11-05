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
 */
/**
 * @apiDefine Activity
 * @apiSuccess {int} id The activity id
 * @apiSuccess {string} name The activity name
 * @apiSuccess {string} slug The activity slug
 * @apiSUccess {string=video,course,test,exercise,summary,flashcard} type The activity type
 * @apiSuccess {string} description The activity description
 * @apiSuccess {bool} visible The activity visibility
 */