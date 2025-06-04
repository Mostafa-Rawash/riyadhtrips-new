<?php

namespace Modules\Contact\Controllers;



use App\Helpers\ReCaptchaEngine;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Mail;

use Matrix\Exception;

use Modules\Contact\Emails\NotificationToAdmin;

use Modules\Contact\Models\Contact;

use Illuminate\Support\Facades\Validator;



class ContactController extends Controller

{

    public function __construct()

    {



    }



    public function index(Request $request)

    {

        $data = [

            'page_title' => __("Contact Page"),

            'header_transparent'=>true,

            'breadcrumbs'       => [

                [

                    'name'  => __('Contact'),

                    'url'  => route('contact.index'),

                    'class' => 'active'

                ],

            ],

        ];

        return view('Contact::index', $data);

    }



    public function store(Request $request)
{
    $request->validate([
        'email' => [
            'required',
            'max:255',
            'email',
            function ($attribute, $value, $fail) {
                $blockedDomains = ['spamemail.com', 'malicious.com'];
                foreach ($blockedDomains as $domain) {
                    if (str_ends_with($value, '@' . $domain)) {
                        $fail(__('This email domain is not allowed.'));
                    }
                }
            },
        ],
        'name' => ['required', 'max:255'],
        // 'phone' => ['required', 'regex:/^\d+$/', 'min:10', 'max:15'],
        'message' => ['required', 'max:500'], // Optional max length
    ]);

    // Google reCAPTCHA Validation
    if (ReCaptchaEngine::isEnable()) {
        $captchaResponse = $request->input('g-recaptcha-response');
        if (!$captchaResponse || !ReCaptchaEngine::verify($captchaResponse)) {
            return response()->json([
                'status' => 0,
                'message' => __('Please verify the captcha'),
            ], 400);
        }
    }

    try {
        // dd($request->input('phone.full'));
        // $contact = new Contact($request->only(['email', 'name', 'phone.main', 'message']));
        $contact = new Contact([
            'email' => $request->email,
            'name' => $request->name,
            'phone' => $request->input('phone.full'), // Extract main phone value
            'message' => $request->message,
            'status' => 'sent',
        ]);
        // $contact->status = 'sent';

        if ($contact->save()) {
            // Queue the email for faster response
            dispatch(function () use ($contact) {
                $this->sendEmail($contact);
            });

            return response()->json([
                'status' => 1,
                'message' => __('Thank you for contacting us! We will get back to you soon.'),
            ], 200);
        }
    } catch (\Exception $e) {
        // dd($e->getMessage());
        \Log::error('Error saving contact form: ' . $e->getMessage());
        return response()->json([
            'status' => 0,
            'message' => __('An error occurred while submitting the form. Please try again later.'),
        ], 500);
    }
}



    protected function sendEmail($contact){

        if($admin_email = setting_item('admin_email')){

            try {

                Mail::to($admin_email)->send(new NotificationToAdmin($contact));

            }catch (Exception $exception){

                Log::warning("Contact Send Mail: ".$exception->getMessage());

            }

        }

    }



    public function t(){

        return new NotificationToAdmin(Contact::find(1));

    }

}

