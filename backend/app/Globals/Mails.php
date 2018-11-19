<?php
namespace App\Globals;
use DB;
use Carbon\Carbon;
use Mail;

class Mails
{

	public static function send_mail($data)
	{
		Mail::send("emails.send", $data, function ($message) use ($data)
		{
			$message->from(env('MAIL_USERNAME'), 'Successpay');
			$message->to($data["member"]->email);
		});
	}

	public static function send_mail_pass($data)
	{
		Mail::send("emails.send_change_member_pass", $data, function ($message) use ($data)
		{
			$message->from(env('MAIL_USERNAME'), 'Successpay');
			$message->to($data["member"]->email);
			$message->subject("Your password has been changed");
		});
	}

	public static function send_mail_activate($data)
	{
		Mail::send("emails.send_activate_member", $data, function ($message) use ($data)
		{
			$message->from(env('MAIL_USERNAME'), 'Successpay');
			$message->to($data["member"]->email);
			if($data["member"]->status_account == 1)
			{
				$message->subject("Account Activation");
			}
			else
			{
				$message->subject("Account Deactivation");
			}
		});
	}

	public static function send_register_verification($data)
	{
		
		Mail::send("emails.send_register_verification", $data, function ($message) use ($data)
		{
			$message->from(env('MAIL_USERNAME'), 'Successpay');
			$message->to($data["email"]->verification_email);
			$message->subject("Verify your email address");
		});
	}

	public static function send_forgot_password_verification($data)
	{
		
		Mail::send("emails.send_forgot_password_verification", $data, function ($message) use ($data)
		{
			$message->from(env('MAIL_USERNAME'), 'Successpay');
			$message->to($data["verify"]->verification_credential);
			$message->subject("Reset Password Code");
		});
	}

	public static function send_email_email_verify($data)
	{
		
		Mail::send("emails.send_register_verification", $data, function ($message) use ($data)
		{
			$message->from(env('MAIL_USERNAME'), 'Successpay');
			$message->to($data["email"]->verification_email);
			$message->subject("Verify your email address");
		});
	}

	public static function send_reset_password_request($data)
	{
		Mail::send("emails.send_reset_password_request", $data, function ($message) use ($data)
		{
			$message->from(env('MAIL_USERNAME'), 'Successpay');
			$message->to($data["member"]->email);
			$message->subject("Reset Password Request");
		});
	}

	public static function send_business_applicaiton_verification($data)
	{
		Mail::send("emails.send_business_application", $data, function ($message) use ($data)
		{
			$message->from(env('MAIL_USERNAME'), 'Successpay');
			$message->to($data["request"]->business_contact_email);
			$message->subject("Business Application");
		});
	}

	public static function promote_career($data)
	{
		Mail::send("emails.promote_career", $data, function ($message) use ($data)
		{
			$message->from(env('MAIL_USERNAME'), 'Successpay');
			$message->to($data["member"]->email);
			$message->subject("Career Promotion");
		});
	}

	public static function order_placed($data)
	{
		Mail::send("emails.order_placed", $data, function ($message) use ($data)
		{
			$message->from(env('MAIL_USERNAME'), 'Successpay');
			$message->to($data["member"]->email);
			$message->subject("Success Mall Token Order");
		});
	}

	public static function order_accepted($data)
	{
		Mail::send("emails.order_accepted", $data, function ($message) use ($data)
		{
			$message->from(env('MAIL_USERNAME'), 'Successpay');
			$message->to($data["member"]->email);
			$message->subject("Success Mall Token Accepted");
		});
	}

	public static function send_contact_us($data)
	{
		Mail::send("emails.send_contact_us", $data, function ($message) use ($data)
		{
			if (env('MAIL_HOST') == 'smtp.mailtrap.io') 
			{
				$message->from($data['request']["email"], $data['request']["name"]);
			}
			else
			{
				$message->from($data['request']["email"], $data['request']["name"]);
			}

			$message->to(env('MAIL_USERNAME'));
			$message->subject("Contact Us");
		});
	}

	public static function send_transfer_token($data)
	{
		Mail::send("emails.send_transfer_token", $data, function ($message) use ($data)
		{
			$message->from(env('MAIL_USERNAME'), 'Successpay');
			$message->to($data["info"]->email);
			$message->subject("Success Mall Manual Token Transfer");	
		});
	}

	public static function send_email_verification($data)
	{
		Mail::send("emails.send_email_verification_kyc", $data, function ($message) use ($data)
		{
			$message->from(env('MAIL_USERNAME'), 'Successpay');
			$message->to($data["email"]->verification_email);
			$message->subject("Verify your email address");
		});
	}

	public static function send_temp_pass($data)
	{
		Mail::send("emails.send_temp_password", $data, function ($message) use ($data)
		{
			$message->from(env('MAIL_USERNAME'), 'Successpay');
			$message->to($data["member"]->email);
			$message->subject("Your temporary password");
		});
	}
}