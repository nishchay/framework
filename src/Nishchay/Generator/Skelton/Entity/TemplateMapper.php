<?php

namespace Nishchay\Generator\Skelton\Entity;

/**
 * Template mapper class controller.
 *
 * @license     https://nishchay.io/license New BSD License
 * @copyright   (c) 2020, Nishchay PHP Framework
 * @version     1.0
 * @author      Bhavik Patel
 */
class TemplateMapper
{

    /**
     * Returns template mapping to class if it does exist.
     * 
     * @param string $template
     * @return array
     * @throws \Exception
     */
    public function getMapping($template)
    {
        $method = 'get' . ucfirst(strtolower($template));
        if (method_exists($this, $method)) {
            return call_user_func([$this, $method]);
        }

        return false;
    }

    /**
     * Returns mapping for hostel entity template.
     * 
     * @return array
     */
    private function getHostel()
    {
        return [
            'building' => Hostel\Building::class,
            'fees' => Hostel\Fees::class,
            'furniture' => Hostel\Furniture::class,
            'hostel' => Hostel\Hostel::class,
            'mess' => Hostel\Mess::class,
            'room' => Hostel\Room::class,
            'student' => Hostel\Student::class,
            'visitor' => Hostel\Visitor::class
        ];
    }

    /**
     * Returns mapping for employee entity template.
     * 
     * @return array
     */
    private function getEmployee()
    {
        return [
            'employee' => Employee\Employee::class,
            'attendance' => Employee\Attendance::class,
            'salary' => Employee\Salary::class,
            'appraisal' => Employee\Appraisal::class
        ];
    }

    /**
     * Returns mapping for category entity template.
     * 
     * @return array
     */
    private function getCategory()
    {
        return [
            'category' => Category::class
        ];
    }

    /**
     * Returns mapping for tree entity template.
     * 
     * @return array
     */
    private function getTree()
    {
        return [
            'tree' => Tree::class
        ];
    }

    /**
     * Returns mapping for user entity template.
     * 
     * @return array
     */
    private function getUser()
    {
        return [
            'user' => User\User::class,
            'userPassword' => User\UserPassword::class,
            'session' => User\Session::class,
            'userPrivacy' => User\UserPrivacy::class,
            'privacyMember' => User\PrivacyMember::class,
            'userPermission' => User\UserPermission::class
        ];
    }

    /**
     * Returns mapping for activity entity template.
     * 
     * @return array
     */
    private function getActivity()
    {
        return [
            'activity' => Activity\Activity::class,
            'affectedEntity' => Activity\AffectedEntity::class,
            'affectedProperty' => Activity\AffectedProperty::class
        ];
    }

    /**
     * Returns mapping for message entity template.
     * 
     * @return array
     */
    private function getMessage()
    {
        return [
            'thread' => Message\Thread::class,
            'threadMember' => Message\ThreadMember::class,
            'message' => Message\Message::class,
            'messageAttachment' => Message\MessageAttachment::class
        ];
    }

    /**
     * Returns mapping for message entity template.
     * 
     * @return array
     */
    private function getPost()
    {
        return [
            'post' => Post\Post::class,
            'postCategory' => Post\PostCategory::class,
            'postAttachment' => Post\Attachment::class,
            'postComment' => Post\Comment::class
        ];
    }

    /**
     * Returns mapping for message entity template.
     * 
     * @return array
     */
    private function getAsset()
    {
        return [
            'asset' => Asset\Asset::class,
            'lifeCycle' => Asset\LifeCycle::class
        ];
    }

}
