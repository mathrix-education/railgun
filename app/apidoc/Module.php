<?php
/**
 * @apiDefine ModuleData
 * @apiParam (Data) {string} name The module name
 * @apiParam (Data) {string} [slug=slug(name)] The module slug
 * @apiParam (Data) {string=course,fill,match,mcq,mcq-table,test,flashcard}} modules.type The module type
 * @apiParam (Data) {mixed} content
 *  [course]    `string` The course content<br>
 *  [fill]      `null`<br>
 *  [match]     `null`<br>
 *  [mcq]       `null`<br>
 *  [mcq-table] `null`<br>
 *  [test]      `null`<br>
 *  [flashcard] `null`
 * @apiParam (Data) {mixed} questions
 *  [course]    `null`<br>
 *  [fill]      `null`<br>
 *  [match]     `string[]`   The static propositions<br>
 *  [mcq]       `string[]`   The mcq questions<br>
 *  [mcq-table] `string[]`   The mcq table questions<br>
 *  [test]      `string[]`   The test questions<br>
 *  [flashcard] `string[]`   The flashcard questions
 * @apiParam (Data) {mixed} choices
 *  [course]    `null`<br>
 *  [fill]      `string[][]` The gap choices matrix<br>
 *  [match]     `string[][]` The draggable propositions matrix<br>
 *  [mcq]       `string[][]` The mcq choices (first dimension for the question number, second dimension for the
 *                           question choices)<br>
 *  [mcq-table] `string[]`   The common choices for all questions<br>
 *  [test]      `null`<br>
 *  [flashcard] `null`
 * @apiParam (Data) {mixed} solutions
 *  [course]    `null`<br>
 *  [fill]      `int[]`      The solution for each question<br>
 *  [match]     `int[][]`    The solutions matrix<br>
 *  [mcq]       `int[][]`    The solutions for each question (second dimension array represents the right
 *                           answers)<br>
 *  [mcq-table] `int[][]`    The solutions for each question (second dimension array represents the right
 *                           answers)<br>
 *  [test]      `string[]`   The solutions for each question<br>
 *  [flashcard] `null`
 * @apiParam (Data) {mixed} corrections
 *  [course]    `null`<br>
 *  [fill]      `string[]`   The corrections for each question<br>
 *  [match]     `string[]`   The corrections for each question<br>
 *  [mcq]       `string[][]` The corrections for each question (second dimension array represents a step)<br>
 *  [mcq-table] `string[]`   The corrections for each question (second dimension array represents a step)<br>
 *  [test]      `string[][]` The corrections for each question (second dimension array represents a step)<br>
 *  [flashcard] `string[]`   The corrections for each question
 * @apiParam (Data) {mixed} options The modules options
 * @apiParam (Data) {int} options.duration [test] The test duration (in seconds)
 * @apiParam (Data) {array} link The models to link with the notion
 * @apiParam (Data) {int[]} link.activities The activities ids to link
 * @apiParam (Data) {array} unlink The models to unlink with the notion
 * @apiParam (Data) {int[]} unlink.activities The activities ids to unlink
 */
/**
 * @apiDefine ModuleArray
 * @apiSuccess {Module[]} modules Array of modules
 * @apiSuccess {int} modules.id The module id
 * @apiSuccess {string} modules.name The module name
 * @apiSuccess {string} modules.slug The module slug
 * @apiSuccess {string=course,fill,match,mcq,mcq-table,test,flashcard} modules.type The module type
 * @apiSuccess {mixed} modules.content
 *  [course]    `string` The course content<br>
 *  [fill]      `null`<br>
 *  [match]     `null`<br>
 *  [mcq]       `null`<br>
 *  [mcq-table] `null`<br>
 *  [test]      `null`<br>
 *  [flashcard] `null`
 * @apiSuccess {mixed} modules.questions
 *  [course]    `null`<br>
 *  [fill]      `null`<br>
 *  [match]     `string[]`   The static propositions<br>
 *  [mcq]       `string[]`   The mcq questions<br>
 *  [mcq-table] `string[]`   The mcq table questions<br>
 *  [test]      `string[]`   The test questions<br>
 *  [flashcard] `string[]`   The test questions
 * @apiSuccess {mixed} modules.choices
 *  [course]    `null`<br>
 *  [fill]      `string[][]` The gap choices matrix<br>
 *  [match]     `string[][]` The draggable propositions matrix<br>
 *  [mcq]       `string[][]` The mcq choices (first dimension for the question number, second dimension for the
 *                           question choices)<br>
 *  [mcq-table] `string[]`   The common choices for all questions<br>
 *  [test]      `null`<br>
 *  [flashcard] `null`
 * @apiSuccess {mixed} modules.solutions
 *  [course]    `null`<br>
 *  [fill]      `int[]`      The solution for each question<br>
 *  [match]     `int[][]`    The solutions matrix<br>
 *  [mcq]       `int[][]`    The solutions for each question (second dimension array represents the right
 *                           answers)<br>
 *  [mcq-table] `int[][]`    The solutions for each question (second dimension array represents the right
 *                           answers)<br>
 *  [test]      `string[]`   The solutions for each question<br>
 *  [flashcard] `null`<br>
 * @apiSuccess {mixed} modules.corrections
 *  [course]    `null`<br>
 *  [fill]      `string[]`   The corrections for each question<br>
 *  [match]     `string[]`   The corrections for each question<br>
 *  [mcq]       `string[][]` The corrections for each question (second dimension array represents a step)<br>
 *  [mcq-table] `string[]`   The corrections for each question (second dimension array represents a step)<br>
 *  [test]      `string[][]` The corrections for each question (second dimension array represents a step)<br>
 *  [flashcard] `string[]`   The corrections for each question
 * @apiSuccess {mixed} modules.options The modules options
 * @apiSuccess {int} modules.options.duration [test] The test duration (in seconds)
 */
/**
 * @apiDefine Module
 * @apiSuccess {int} id The module id
 * @apiSuccess {string} name The module name
 * @apiSuccess {string} slug The module slug
 * @apiSuccess {string=course,fill,match,mcq,mcq-table,test,flashcard} modules.type The module type
 * @apiSuccess {mixed} content
 *  [course]    `string` The course content<br>
 *  [fill]      `null`<br>
 *  [match]     `null`<br>
 *  [mcq]       `null`<br>
 *  [mcq-table] `null`<br>
 *  [test]      `null`<br>
 *  [flashcard] `null`
 * @apiSuccess {mixed} questions
 *  [course]    `null`<br>
 *  [fill]      `null`<br>
 *  [match]     `string[]`   The static propositions<br>
 *  [mcq]       `string[]`   The mcq questions<br>
 *  [mcq-table] `string[]`   The mcq table questions<br>
 *  [test]      `string[]`   The test questions<br>
 *  [flashcard] `string[]`   The flashcard questions
 * @apiSuccess {mixed} choices
 *  [course]    `null`<br>
 *  [fill]      `string[][]` The gap choices matrix<br>
 *  [match]     `string[][]` The draggable propositions matrix<br>
 *  [mcq]       `string[][]` The mcq choices (first dimension for the question number, second dimension for the
 *                           question choices)<br>
 *  [mcq-table] `string[]`   The common choices for all questions<br>
 *  [test]      `null`<br>
 *  [flashcard] `null`
 * @apiSuccess {mixed} solutions
 *  [course]    `null`<br>
 *  [fill]      `int[]`      The solution for each question<br>
 *  [match]     `int[][]`    The solutions matrix<br>
 *  [mcq]       `int[][]`    The solutions for each question (second dimension array represents the right answers)<br>
 *  [mcq-table] `int[][]`    The solutions for each question (second dimension array represents the right answers)<br>
 *  [test]      `string[]`   The solutions for each question<br>
 *  [flashcard] `null`
 * @apiSuccess {mixed} corrections
 *  [course]    `null`<br>
 *  [fill]      `string[]`   The corrections for each question<br>
 *  [match]     `string[]`   The corrections for each question<br>
 *  [mcq]       `string[][]` The corrections for each question (second dimension array represents a step)<br>
 *  [mcq-table] `string[]`   The corrections for each question<br>
 *  [test]      `string[][]` The corrections for each question (second dimension array represents a step)<br>
 *  [flashcard] `string[]`   The corrections for each question
 * @apiSuccess {mixed} options The modules options
 * @apiSuccess {int} options.duration [test] The test duration (in seconds)
 */