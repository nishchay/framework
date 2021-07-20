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
#[Routing(prefix: 'post')]
class Post
{

    /**
     * To render list of posts.
     * 
     */
    #[Route(path: '/', type: 'GET')]
    public function index()
    {
        
    }

    /**
     * Create post.
     * 
     */
    #[Route(path: '/', type: 'POST')]
    public function create()
    {
        
    }

    /**
     * View post.
     * 
     */
    #[Route(path: '{postId}', type: 'GET')]
    #[Placeholder(['postId' => 'int'])]
    public function viewPost(int $postId)
    {
        
    }

    /**
     * Like post.
     * 
     */
    #[Route(path: '{postId}', type: 'POST')]
    #[Placeholder(['postId' => 'int'])]
    public function likePost(int $postId)
    {
        
    }

    /**
     * Update post.
     * 
     */
    #[Route(path: '{postId}', type: 'PUT')]
    #[Placeholder(['postId' => 'int'])]
    public function editPost(int $postId)
    {
        
    }

    /**
     * Remove post.
     * 
     */
    #[Route(path: '{postId}', type: 'DELETE')]
    #[Placeholder(['postId' => 'int'])]
    public function removePost(int $postId)
    {
        
    }

    /**
     * View List of comment.
     * 
     */
    #[Route(path: '{postId}/comment', type: 'GET')]
    #[Placeholder(['postId' => 'int'])]
    public function viewComments(int $postId)
    {
        
    }

    /**
     * Add comment.
     * 
     */
    #[Route(path: '{postId}/comment', type: 'POST')]
    #[Placeholder(['postId' => 'int'])]
    public function addComment(int $postId)
    {
        
    }

    /**
     * Like comment.
     * 
     */
    #[Route(path: '{postId}/comment/{commentId}/like', type: 'POST')]
    #[Placeholder(['postId' => 'int', 'commentId' => 'int'])]
    public function likeComment(int $postId, int $commentId)
    {
        
    }

    /**
     * Update comment.
     * 
     */
    #[Route(path: '{postId}/comment/{commentId}', type: 'PUT')]
    #[Placeholder(['postId' => 'int', 'commentId' => 'int'])]
    public function editComment(int $postId, int $commentId)
    {
        
    }

    /**
     * Remove comment.
     * 
     */
    #[Route(path: '{postId}/comment/{commentId}', type: 'DELETE')]
    #[Placeholder(['postId' => 'int', 'commentId' => 'int'])]
    public function removeComment(int $postId, int $commentId)
    {
        
    }

}
