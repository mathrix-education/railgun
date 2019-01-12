<?php
namespace App\Controllers;

/**
 * Class PurchaseController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Controllers
 */
class PurchaseController extends BaseController
{
    /**
     * @apiVersion      4.3.0
     * @api       {get} /purchases/{?page}/{?perPage} Index
     * @apiGroup        Purchase
     * @apiName         PurchasesIndex
     * @apiDescription  Retrieve purchases
     * @apiPermission   moderator
     * @apiUse          Pagination
     * @apiUse          PurchaseArray
     * @apiUse          Http401Unauthorized
     */

    /**
     * @apiVersion      4.3.0
     * @api       {get} /purchases/{id} Get
     * @apiGroup        Purchases
     * @apiName         PurchaseGet
     * @apiDescription  Get a purchase by id
     * @apiPermission   public
     * @apiParam  {int} id The purchase id
     * @apiUse          PurchaseModel
     * @apiUse          Http404ModelNotFound
     */

    /**
     * @apiVersion      4.3.0
     * @api      {post} /purchases Add
     * @apiGroup        Purchases
     * @apiName         PurchaseAdd
     * @apiDescription  Add a purchase
     * @apiPermission   moderator
     * @apiUse          Bearer
     * @apiUse          PurchaseRequest
     * @apiUse          PurchaseModel
     * @apiUse          Http401Unauthorized
     * @apiUse          Http422ModelValidation
     * @apiUse          Http422ModelSave
     */

    /**
     * @apiVersion      4.3.0
     * @api     {patch} /purchases/{id} Edit
     * @apiGroup        Purchases
     * @apiName         PurchaseEdit
     * @apiDescription  Edit a purchase
     * @apiPermission   moderator
     * @apiUse          Bearer
     * @apiParam  {int} id The purchase id
     * @apiUse          PurchaseRequest
     * @apiUse          PurchaseModel
     * @apiUse          Http401Unauthorized
     * @apiUse          Http404ModelNotFound
     * @apiUse          Http422ModelSave
     * @apiUse          Http422ModelValidation
     */

    /**
     * @apiVersion      4.3.0
     * @api    {delete} /purchases/{id} Delete
     * @apiGroup        Purchases
     * @apiName         PurchaseDelete
     * @apiDescription  Delete a purchase
     * @apiUse          Bearer
     * @apiPermission   moderator
     * @apiParam  {int} id The purchase id
     * @apiUse          Http401Unauthorized
     * @apiUse          Http404ModelNotFound
     */
}
