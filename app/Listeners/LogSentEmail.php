<?php

namespace App\Listeners;

use App\Models\SentEmail;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Auth;

class LogSentEmail
{
    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        try {
            // In Laravel 11, $event->sent is a SentMessage object
            // We need to get the original message using getOriginalMessage()
            $sentMessage = $event->sent;
            $message = method_exists($sentMessage, 'getOriginalMessage') 
                ? $sentMessage->getOriginalMessage() 
                : $sentMessage;
            
            // Get recipients
            $toAddresses = method_exists($message, 'getTo') ? $message->getTo() : [];
            $to = !empty($toAddresses) ? array_keys($toAddresses)[0] : null;
            
            // Get from
            $fromAddresses = method_exists($message, 'getFrom') ? $message->getFrom() : [];
            $fromAddress = !empty($fromAddresses) ? array_keys($fromAddresses)[0] : null;
            $fromName = null;
            if (!empty($fromAddresses) && $fromAddress) {
                $fromObj = $fromAddresses[$fromAddress];
                $fromName = is_object($fromObj) && method_exists($fromObj, 'getName') 
                    ? $fromObj->getName() 
                    : (is_string($fromObj) ? $fromObj : null);
            }
            
            // Get subject
            $subject = method_exists($message, 'getSubject') ? ($message->getSubject() ?? '') : '';
            
            // Get body
            $bodyHtml = null;
            $bodyText = null;
            if (method_exists($message, 'getHtmlBody') && $message->getHtmlBody()) {
                $bodyHtml = $message->getHtmlBody();
                $bodyText = strip_tags($bodyHtml);
            } elseif (method_exists($message, 'getTextBody') && $message->getTextBody()) {
                $bodyText = $message->getTextBody();
            }
            
            // Get headers
            $headers = [];
            try {
                if (method_exists($message, 'getHeaders')) {
                    $messageHeaders = $message->getHeaders();
                    if ($messageHeaders && method_exists($messageHeaders, 'all')) {
                        foreach ($messageHeaders->all() as $header) {
                            $headers[$header->getName()] = method_exists($header, 'getBodyAsString') 
                                ? $header->getBodyAsString() 
                                : (string)$header;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Ignore header errors
            }
            
            // Get attachments
            $attachments = [];
            try {
                if (method_exists($message, 'getAttachments')) {
                    foreach ($message->getAttachments() as $attachment) {
                        $attachments[] = [
                            'filename' => method_exists($attachment, 'getFilename') ? $attachment->getFilename() : 'unknown',
                            'content_type' => method_exists($attachment, 'getContentType') ? $attachment->getContentType() : 'application/octet-stream',
                            'size' => method_exists($attachment, 'getBody') ? strlen($attachment->getBody()) : 0,
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Ignore attachment errors
            }
            
            // Try to get related model from message data
            $relatedType = null;
            $relatedId = null;
            if (isset($event->data['proposal'])) {
                $relatedType = 'Proposal';
                $relatedId = $event->data['proposal']->id ?? null;
            }
            
            if ($to) {
                SentEmail::create([
                    'to' => $to,
                    'from' => $fromAddress,
                    'from_name' => $fromName,
                    'subject' => $subject,
                    'body_html' => $bodyHtml,
                    'body_text' => $bodyText,
                    'headers' => !empty($headers) ? $headers : null,
                    'attachments' => !empty($attachments) ? $attachments : null,
                    'status' => 'sent',
                    'user_id' => Auth::id(),
                    'related_type' => $relatedType,
                    'related_id' => $relatedId,
                    'sent_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            // Log error but don't break email sending
            \Log::error('Failed to log sent email: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}
