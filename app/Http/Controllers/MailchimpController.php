<?php
namespace App\Http\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Newsletter\NewsletterFacade;

/**
 * Class MailchimpController
 *
 * @author Mathrix Back-End Team
 * @package App\Http\Controllers
 * @since 0.8.1
 * @copyright Mathrix
 */
class MailchimpController extends BaseController
{
    /**
     * @apiVersion 0.8.1
     * @api {post} /newsletter/subscribe/{list}
     * @apiGroup Newsletters
     * @apiDescription Subscribe an email to a Mailchimp list
     * @apiPermission registered
     * @apiUse Bearer
     * @apiParam {string=subscribers,parents,profs} list
     * @apiUse Http400InvalidRequest
     *
     * @param Request $request
     * @param string $list
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     */
    public function subscribe(Request $request, string $list)
    {
        $email = $request->input("email");
        if ($email === null) {
            throw new Http400InvalidRequestException("email field is required");
        }

        $response = NewsletterFacade::subscribeOrUpdate($email, [], $list);

        throw_if($response === false, Http400InvalidRequestException::class,
            "Mailchimp subscription failed");

        return response()->json($response);
    }
}