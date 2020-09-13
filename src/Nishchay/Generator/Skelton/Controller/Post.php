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
     * @Route(path='/', type=GET)
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
    public function viewPost($postId = '@Segment(index=postId)')
    {
        
    }

    /**
     * Edit post.
     * 
     * @Route(path='{postId}/like',type=POST)
     * @Placeholder(postId=number)
     * @Response(type=VIEW)
     */
    public function likePost($postId = '@Segment(index=postId)')
    {
        
    }

    /**
     * Edit post.
     * 
     * @Route(path='{postId}',type=PUT)
     * @Placeholder(postId=number)
     * @Response(type=VIEW)
     */
    public function editPost($postId = '@Segment(index=postId)')
    {
        
    }

    /**
     * 
     * @Route(path='{postId}',type=DELETE)
     * @Placeholder(postId=number)
     * @Response(type=JSON)
     */
    public function removePost($postId = '@Segment(index=postId)')
    {
        
    }

    /**
     * View List of comment.
     * 
     * @Route(path='{postId}/comments',type=GET)
     * @Placeholder(postId=number)
     * @Response(type=VIEW)
     */
    public function viewComments($postId = '@Segment(index=postId)')
    {
        
    }

    /**
     * Add comment.
     * 
     * @Route(path='{postId}/comment',type=POST)
     * @Placeholder(postId=number)
     * @Response(type=VIEW)
     */
    public function addComment($postId = '@Segment(index=postId)')
    {
        
    }

    /**
     * Add comment.
     * 
     * @Route(path='{postId}/comment/{commentId}/like',type=POST)
     * @Placeholder(postId=number,commentId=number)
     * @Response(type=VIEW)
     */
    public function likeComment($postId = '@Segment(index=postId)',
            $commentId = '@Segment(index=commentId)')
    {
        
    }

    /**
     * Add comment.
     * 
     * @Route(path='{postId}/comment/{commentId}',type=PUT)
     * @Placeholder(postId=number,commentId=number)
     * @Response(type=VIEW)
     */
    public function editComment($postId = '@Segment(index=postId)',
            $commentId = '@Segment(index=commentId)')
    {
        
    }

    /**
     * Add comment.
     * 
     * @Route(path='{postId}/comment/{commentId}',type=DELETE)
     * @Placeholder(postId=number,commentId=number)
     * @Response(type=VIEW)
     */
    public function removeComment($postId = '@Segment(index=postId)',
            $commentId = '@Segment(index=commentId)')
    {
        
    }

    
}
