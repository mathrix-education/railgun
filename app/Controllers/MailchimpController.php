<?php
namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Newsletter\NewsletterFacade as Newsletter;

/**
 * Class MailchimpController
 *
 * @author Mathrix Back-End Team
 * @package App\Controllers
 * @since 1.0.0
 * @copyright Mathrix SA
 */
class MailchimpController extends BaseController
{
    /**
     * @apiVersion 4.0.0
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
     * @throws \Exception
     */
    public function subscribe(Request $request, string $list)
    {
        $email = $request->input("email");
        if ($email === null) {
            throw new Http400InvalidRequestException("email field is required");
        }

        $response = Newsletter::subscribeOrUpdate($email, [], $list);

        if ($response === false) {
            throw new Http400InvalidRequestException("Mailchimp subscription failed");
        }

        return response()->json($response);
    }
}
