<?php

namespace Nishchay\Generator\Skelton\Controller;

/**
 * Message controller class.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 * @Controller
 * @Routing(prefix='post')
 */
class Post
{

    /**
     * To render list of posts.
     * 
     * @Route(path='/')
     * @Response(type=VIEW)
     */
    public function index()
    {
        
    }

    /**
     * Create post.
     * 
     * @Route(path='/',type=POST)
     * @Response(type=JSON)
     */
    public function create()
    {
        
    }

    /**
     * Edit post.
     * 
     * @Route(path='{postId}',type=GET)
     * @Placeholder(postId=number)
     * @Response(type=VIEW)
     */
    public function viewPost(int $postId)
    {
        
    }

    /**
     * Edit post.
     * 
     * @Route(path='{postId}/like',type=POST)
     * @Placeholder(postId=number)
     * @Response(type=VIEW)
     */
    public function likePost(int $postId)
    {
        
    }

    /**
     * Edit post.
     * 
     * @Route(path='{postId}',type=PUT)
     * @Placeholder(postId=number)
     * @Response(type=VIEW)
     */
    public function editPost(int $postId)
    {
        
    }

    /**
     * 
     * @Route(path='{postId}', type=DELETE)
     * @Placeholder(postId=number)
     * @Response(type=JSON)
     */
    public function removePost(int $postId)
    {
        
    }

    /**
     * View List of comment.
     * 
     * @Route(path='{postId}/comments',type=GET)
     * @Placeholder(postId=number)
     * @Response(type=VIEW)
     */
    public function viewComments(int $postId)
    {
        
    }

    /**
     * Add comment.
     * 
     * @Route(path='{postId}/comment',type=POST)
     * @Placeholder(postId=number)
     * @Response(type=VIEW)
     */
    public function addComment(int $postId)
    {
        
    }

    /**
     * Add comment.
     * 
     * @Route(path='{postId}/comment/{commentId}/like',type=POST)
     * @Placeholder(postId=number,commentId=number)
     * @Response(type=VIEW)
     */
    public function likeComment(int $postId, int $commentId)
    {
        
    }

    /**
     * Add comment.
     * 
     * @Route(path='{postId}/comment/{commentId}',type=PUT)
     * @Placeholder(postId=number,commentId=number)
     * @Response(type=VIEW)
     */
    public function editComment(int $postId, int $commentId)
    {
        
    }

    /**
     * Add comment.
     * 
     * @Route(path='{postId}/comment/{commentId}',type=DELETE)
     * @Placeholder(postId=number,commentId=number)
     * @Response(type=VIEW)
     */
    public function removeComment(int $postId, int $commentId)
    {
        
    }

    
}
