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
     * @Route(see=TRUE)
     * @Response(type=JSON)
     */
    public function create()
    {
        
    }

    /**
     * Edit post.
     * 
     * @Route(path='{postId}')
     * @Placeholder(postId=number)
     * @Response(type=VIEW)
     */
    public function viewPost($postId = '@Segment(index=postId)')
    {
        
    }

    /**
     * Edit post.
     * 
     * @Route(path='{postId}/like')
     * @Placeholder(postId=number)
     * @Response(type=VIEW)
     */
    public function likePost($postId = '@Segment(index=postId)')
    {
        
    }

    /**
     * Edit post.
     * 
     * @Route(path='{postId}/edit')
     * @Placeholder(postId=number)
     * @Response(type=VIEW)
     */
    public function editPost($postId = '@Segment(index=postId)')
    {
        
    }

    /**
     * 
     * @Route(path='{postId}/remove')
     * @Placeholder(postId=number)
     * @Response(type=JSON)
     */
    public function removePost($postId = '@Segment(index=postId)')
    {
        
    }

    /**
     * View List of comment.
     * 
     * @Route(path='{postId}/comments')
     * @Placeholder(postId=number)
     * @Response(type=VIEW)
     */
    public function viewComments($postId = '@Segment(index=postId)')
    {
        
    }

    /**
     * Add comment.
     * 
     * @Route(path='{postId}/comment/add')
     * @Placeholder(postId=number)
     * @Response(type=VIEW)
     */
    public function addComment($postId = '@Segment(index=postId)')
    {
        
    }

    /**
     * Add comment.
     * 
     * @Route(path='{postId}/comment/{commentId}/like')
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
     * @Route(path='{postId}/comment/{commentId}/edit')
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
     * @Route(path='{postId}/comment/{commentId}/remove')
     * @Placeholder(postId=number,commentId=number)
     * @Response(type=VIEW)
     */
    public function removeComment($postId = '@Segment(index=postId)',
            $commentId = '@Segment(index=commentId)')
    {
        
    }

    
}
