<?php

namespace Nishchay\Generator\Skelton\Controller;

use Nishchay\Attributes\Controller\Controller;
use Nishchay\Attributes\Controller\Routing;
use Nishchay\Attributes\Controller\Method\{
    Route,
    Placeholder
};

/**
 * Message controller class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 * 
 */
#[Controller]
#[Routing(prefix: 'message')]
class Message
{

    /**
     */
    #[Route(path: '/', type: 'GET')]
    public function threadList()
    {
        return 'message/list';
    }

    /**
     * You may want to implement this for AJAX so response type is JSON.
     * 
     */
    #[Route(path: '/', type: 'POST')]
    public function create()
    {
        # Here you can create thread along with add thread member.
        # As this skeltonas have addMember route already. You can create thread
        # here and then forward request to message/{threadId}/addMember.
        # and then to send message, forward request again to
        # message/{threadId}/send.
    }

    /**
     * View messages belongs to thread.
     * 
     */
    #[Route(path: '{threadId}', type: 'GET')]
    #[Placeholder(['threadId' => 'int'])]
    public function messageView(int $threadId)
    {
        # Fetch messages belong to thread
    }

    /**
     * You may want to implement this for AJAX so response type is JSON.
     * 
     */
    #[Route(path: '{threadId}/member', type: 'POST')]
    #[Placeholder(['threadId' => 'int'])]
    public function addMember(int $threadId)
    {
        # Add Member to message thread
        # You might need following request parameter.
        /*
         * 1. thread_id 
         * 2. member_id
         * 3. is_admin
         */
    }

    /**
     * You may want to implement this for AJAX so response type is JSON.
     * 
     */
    #[Route(path: '{threadId}/member', type: 'DELETE')]
    #[Placeholder(['threadId' => 'int'])]
    public function removeMember(int $threadId)
    {
        # Remove member from message thread
        # You might need following request parameter.
        /*
         * 1. thread_id
         * 2. member_id
         * 3. remove_reason
         */
    }

    /**
     * You may want to implement this for AJAX so response type is JSON.
     * 
     */
    #[Route(path: '{threadId}/leave', type: 'DELETE')]
    #[Placeholder(['threadId' => 'int'])]
    public function leave(int $threadId)
    {
        # To leave specified thread.
        # This is for remove yourself from thread
    }

    /**
     * You may want to implement this for AJAX so response type is JSON.
     * 
     */
    #[Route(path: '{threadId}/send', type: 'POST')]
    #[Placeholder(['threadId' => 'int'])]
    public function send(int $threadId)
    {
        # Send message to thread.
        # You might need following request parameter.
        /*
         * 1. messageContent
         * 2. messageType 
         */
    }

    /**
     * Remove thread.
     * 
     */
    #[Route(path: '{threadId}', type: 'DELETE')]
    #[Placeholder(['threadId' => 'int'])]
    public function removeThread(int $threadId)
    {
        # Remove thread
    }

    /**
     * Remove thread.
     * 
     */
    #[Route(path: '{threadId}/{messageId}', type: 'DELETE')]
    #[Placeholder(['threadId' => 'int', 'messageId' => 'int'])]
    public function removeMessage(int $threadId, int $messageId)
    {
        # Remove message from thread
    }

    /**
     */
    #[Route(path: '{threadId}/{messageId}/read', type: 'PUT')]
    #[Placeholder(['threadId' => 'int', 'messageId' => 'int'])]
    public function markRead(int $threadId, int $messageId)
    {
        # Implement message to mark as read.
        # You can implement list of messge or thread to be marked as read.
    }

}
