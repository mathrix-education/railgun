<?php
/**
 * @apiDefine ModuleArray
 * @apiSuccess {Module[]} modules Array of modules
 * @apiSuccess {int} modules.id The module id
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
 * @apiSuccess {bool} modules.visible The module visibility
 * @apiSuccess {mixed} modules.options The module options
 * @apiSuccess {int} modules.options.duration [test] The test duration (in seconds)
 */
