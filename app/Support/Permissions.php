<?php

namespace App\Support;

class Permissions
{
    public const ROLES = ['superadmin' => 'Superadmin', 'admin' => 'Admin', 'user' => 'Users'];

    public const MODULES = [
        'rooms' => 'Rooms',
        'restaurant' => 'Restaurant Management',
        'packages' => 'Packages',
        'experiences' => 'Experiences',
        'hero-slides' => 'Home Carousel',
        'promotions' => 'Offers & Advertising',
        'gallery' => 'Gallery',
        'services' => 'Services',
        'testimonials' => 'Testimonials',
        'faqs'         => 'FAQs',
        'blogs'        => 'Blogs',
        'enquiries' => 'Enquiries',
        'settings' => 'Site Settings',
    ];
}
