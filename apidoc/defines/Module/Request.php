<?php
/**
 * @apiDefine ModuleRequest
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
 * @apiParam (Data) {bool} visible The module visibility
 * @apiParam (Data) {mixed} options The modules options
 * @apiParam (Data) {int} options.duration [test] The test duration (in seconds)
 * @apiParam (Data) {array} [link] The models to link with the notion
 * @apiParam (Data) {int[]} [link.activities] The activities ids to sync with the module. The given order wll be used to set
 * the order in the many-to-many relationship. If not specified, do not change anything.
 * @apiParam (Data) {int[]} [link.sections] The sections ids to sync with the module. The given order wll be used to set
 * the order in the many-to-many relationship. If not specified, do not change anything.
 */
