<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ContactUsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Frontend/contactus/index', [
            'page' => [
                'title' => 'Contact Us',
                'eyebrow' => 'Get in touch',
                'subtitle' => 'We would be pleased to assist you with product inquiries, order support, and all general questions related to our store.',
                'breadcrumb' => [
                    ['label' => 'Home', 'href' => '/'],
                    ['label' => 'Contact Us', 'href' => null],
                ],
            ],
            'contact' => [
                'address' => 'No. 522/c, Aggona junction/baththramulla',
                'phones' => [
                    '0765807548',
                    '0711442211',
                    '0773516451',
                ],
                'email' => 'froziohub@gmail.com',
                'map_title' => 'We are located at',
                'map_url' => 'https://www.google.com/maps/place/6%C2%B054%2755.9%22N+79%C2%B055%2725.2%22E/@6.9155186,79.9229038,18z/data=!3m1!4b1!4m18!1m13!4m12!1m4!2m2!1d79.8614748!2d6.9174106!4e1!1m6!1m2!1s0x3ae2575eb9b9d7ff:0x1bd44b26cb11038b!2sAggona+Junction,+Kalapaluwawa+Rd,+Kolonnawa!2m2!1d79.9236864!2d6.9164525!3m3!8m2!3d6.915517!4d79.923662!5m2!1e4!1e2?entry=ttu&g_ep=EgoyMDI2MDMxOC4xIKXMDSoASAFQAw%3D%3D',
                'map_embed_url' => 'https://maps.google.com/maps?q=6.915517,79.923662&z=17&output=embed',
            ],
        ]);
    }
}