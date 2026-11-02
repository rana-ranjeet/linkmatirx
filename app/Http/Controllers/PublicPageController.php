<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicPageController extends Controller
{
    public function __invoke(Request $request): View
    {
        $pages = [
            'services' => [
                'eyebrow' => 'Publisher marketplace',
                'title' => 'Publishing services, in one place',
                'intro' => 'Find websites offering sponsored articles and link insertion. Each publisher sets their own listing details and service price.',
                'cards' => [
                    ['title' => 'Guest posts', 'body' => 'Compare publisher requirements and pricing, then share the brief and destination URL for your campaign.'],
                    ['title' => 'Link insertion', 'body' => 'Find approved publisher listings that offer link insertion and review their current prices.'],
                    ['title' => 'Publisher partnerships', 'body' => 'Publishers can list multiple websites and submit changes for review before they appear publicly.'],
                ],
                'cta' => 'Explore the marketplace',
                'route' => 'marketplace.index',
            ],
            'how-it-works' => [
                'eyebrow' => 'How it works',
                'title' => 'A clear path from discovery to delivery',
                'intro' => 'LINKVIO brings advertisers and publishers together around transparent website listings and an organized collaboration process.',
                'cards' => [
                    ['title' => '01 · Find a fit', 'body' => 'Search approved publisher websites by niche, country, language, service, metrics, and price.'],
                    ['title' => '02 · Review the listing', 'body' => 'Check the publisher description, service options, and available provider-sourced metrics.'],
                    ['title' => '03 · Work with confidence', 'body' => 'Save publishers and manage your account in your role-specific workspace. Order checkout and delivery workflows are being added separately.'],
                ],
                'cta' => 'Browse publisher websites',
                'route' => 'marketplace.index',
            ],
            'pricing' => [
                'eyebrow' => 'Transparent pricing',
                'title' => 'Compare prices set by publishers',
                'intro' => 'Placement prices vary by website and service. Listings show the publisher’s price and currency; LINKVIO does not invent ratings, traffic, or service fees.',
                'cards' => [
                    ['title' => 'Guest post pricing', 'body' => 'See the listed guest post price on each approved website that offers the service.'],
                    ['title' => 'Link insertion pricing', 'body' => 'Compare insertion prices independently. A publisher may offer one service, both services, or neither.'],
                    ['title' => 'Platform fees', 'body' => 'Checkout and platform commission rules are not active yet. No additional platform fee is represented as payable on this preview.'],
                ],
                'cta' => 'Compare live listings',
                'route' => 'marketplace.index',
            ],
            'about' => [
                'eyebrow' => 'About LINKVIO',
                'title' => 'Better publisher partnerships start with clarity',
                'intro' => 'LINKVIO is building a marketplace where advertisers can discover publisher websites and publishers can present their services with clear, reviewable details.',
                'cards' => [
                    ['title' => 'Useful information', 'body' => 'Listings distinguish verified details from metrics that are unavailable or have not been supplied by a provider.'],
                    ['title' => 'Fair participation', 'body' => 'Advertisers and publishers use separate account roles and workspaces.'],
                    ['title' => 'Careful growth', 'body' => 'Listings are reviewed before appearing in public search.'],
                ],
                'cta' => 'See the marketplace',
                'route' => 'marketplace.index',
            ],
            'faqs' => [
                'eyebrow' => 'Frequently asked questions',
                'title' => 'A few things to know',
                'intro' => 'Here are answers about the marketplace as it works today.',
                'cards' => [
                    ['title' => 'Are all websites public?', 'body' => 'No. Only active websites approved by an administrator and technically verified for ownership appear in public search. An authorized administrator can explicitly override verification and that decision is audited.'],
                    ['title' => 'Where do SEO metrics come from?', 'body' => 'Metrics are displayed only when supplied with a named provider and update date. Missing values show “Not available.”'],
                    ['title' => 'Can I order a placement right now?', 'body' => 'The directory, publisher listing workflow, and favorites work now. Secure checkout and order fulfillment are still being implemented.'],
                    ['title' => 'How do I list my website?', 'body' => 'Create a publisher account, verify your email, submit your website for administrator review, then add the generated ownership-verification meta tag to your homepage.'],
                ],
                'cta' => 'Browse websites',
                'route' => 'marketplace.index',
            ],
            'privacy' => [
                'eyebrow' => 'Privacy',
                'title' => 'Privacy notice',
                'intro' => 'This project is in development. Before collecting personal information from production users, the operator must publish a reviewed privacy notice explaining data use, retention, processors, user rights, and contact details.',
                'cards' => [],
                'cta' => 'Contact LINKVIO',
                'route' => 'contact',
            ],
            'terms' => [
                'eyebrow' => 'Terms',
                'title' => 'Terms of service',
                'intro' => 'This project is in development. Production use requires reviewed terms covering marketplace responsibilities, payments, publisher content, disputes, and applicable law.',
                'cards' => [],
                'cta' => 'Contact LINKVIO',
                'route' => 'contact',
            ],
            'refund-policy' => [
                'eyebrow' => 'Refunds',
                'title' => 'Refund policy',
                'intro' => 'Secure checkout and refund processing are not active yet. A production refund policy will be published alongside the supported payment provider and order workflow.',
                'cards' => [],
                'cta' => 'Contact LINKVIO',
                'route' => 'contact',
            ],
            'cookie-policy' => [
                'eyebrow' => 'Cookies',
                'title' => 'Cookie notice',
                'intro' => 'This preview uses essential application cookies for secure sessions. Configure and disclose any additional analytics or advertising cookies before enabling them in production.',
                'cards' => [],
                'cta' => 'Contact LINKVIO',
                'route' => 'contact',
            ],
        ];

        $page = $pages[$request->route('page')] ?? abort(404);

        return view('public.page', [
            'slug' => $request->route('page'),
            ...$page,
        ]);
    }
}
