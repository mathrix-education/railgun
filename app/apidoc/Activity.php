<?php
/**
 * @apiDefine ActivityData
 * @apiParam (Data) {string} name The activity name
 * @apiParam (Data) {string} [slug=slug(name)] The activity slug
 * @apiParam (Data) {string} description The activity description
 * @apiParam (Data) {string=video,course,test,exercise,summary,flashcard} type The activity type
 * @apiParam (Data) {bool} visible The activity visibility
 * @apiParam (Data) {array} link The models to link with the notion
 * @apiParam (Data) {int[]} link.modules The modules ids to link
 * @apiParam (Data) {int[]} link.notions The notions ids to link
 * @apiParam (Data) {array} unlink The models to unlink with the notion
 * @apiParam (Data) {int[]} unlink.modules The modules ids to unlink
 * @apiParam (Data) {int[]} unlink.notions The notions ids to unlink
 */
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