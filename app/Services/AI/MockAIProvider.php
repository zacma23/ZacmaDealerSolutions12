<?php

namespace App\Services\AI;

class MockAIProvider implements AIProviderInterface
{
    public function getProviderName(): string
    {
        return 'mock';
    }

    public function generateText(string $prompt, array $options = []): array
    {
        $promptLower = strtolower($prompt);
        $text = '';

        if (!empty($options['json_mode']) || str_contains($promptLower, 'detect listing') || str_contains($promptLower, 'analyze listing')) {
            if (str_contains($promptLower, 'house') || str_contains($promptLower, 'villa') || str_contains($promptLower, 'apartment') || str_contains($promptLower, 'property')) {
                $payload = [
                    'title' => 'Modern 4-Bedroom Luxury Villa in Bole',
                    'category_slug' => 'real-estate',
                    'suggested_price' => 28500000,
                    'currency' => 'ETB',
                    'price_type' => 'negotiable',
                    'city' => 'Addis Ababa',
                    'address' => 'Bole Sub-city, Near Rwanda Embassy',
                    'description' => 'Architecturally designed modern 4-bedroom luxury villa. Features spacious en-suite master bedroom, contemporary open-concept kitchen, landscaped garden, backup generator, water reservoir, and 24/7 security. Ready for immediate occupancy.',
                    'fields' => [
                        'Property Type' => 'Villa',
                        'Bedrooms' => '4',
                        'Bathrooms' => '4.5',
                        'Area (sqm)' => '450',
                        'Furnished' => 'Semi-Furnished',
                    ],
                    'features' => ['Backup Generator', 'Water Tank', 'Parking for 4 Cars', 'Security Gate', 'Modern Kitchen']
                ];
            } elseif (str_contains($promptLower, 'phone') || str_contains($promptLower, 'laptop') || str_contains($promptLower, 'computer') || str_contains($promptLower, 'electronics')) {
                $payload = [
                    'title' => 'Apple MacBook Pro 16" M3 Pro 36GB / 512GB Space Black',
                    'category_slug' => 'electronics',
                    'suggested_price' => 380000,
                    'currency' => 'ETB',
                    'price_type' => 'fixed',
                    'city' => 'Addis Ababa',
                    'address' => 'Bole Medhanialem Commercial Center',
                    'description' => 'Factory sealed Apple MacBook Pro 16-inch featuring the breakthrough M3 Pro chip. 36GB Unified Memory, 512GB ultra-fast SSD, Liquid Retina XDR display with ProMotion 120Hz. Complete with 1-year warranty and original accessories.',
                    'fields' => [
                        'Brand' => 'Apple',
                        'Model' => 'MacBook Pro 16" M3 Pro',
                        'RAM' => '36 GB',
                        'Storage' => '512 GB SSD',
                        'Condition' => 'Brand New Sealed',
                    ],
                    'features' => ['Liquid Retina XDR', 'MagSafe 3 Charging', 'Three Thunderbolt 4 Ports', '1 Year Warranty']
                ];
            } else {
                $payload = [
                    'title' => '2023 Toyota RAV4 XLE Hybrid AWD',
                    'category_slug' => 'vehicles',
                    'suggested_price' => 5400000,
                    'currency' => 'ETB',
                    'price_type' => 'fixed',
                    'city' => 'Addis Ababa',
                    'address' => 'CMC St. Michael, Dealership Showroom',
                    'description' => 'Immaculate 2023 Toyota RAV4 XLE Hybrid with Intelligent All-Wheel Drive. Delivers phenomenal 40 MPG fuel economy paired with responsive power. Includes Toyota Safety Sense 2.5, blind spot monitoring, push button start, and dual-zone climate control.',
                    'fields' => [
                        'Make' => 'Toyota',
                        'Model' => 'RAV4',
                        'Year' => '2023',
                        'Transmission' => 'Automatic',
                        'Fuel Type' => 'Hybrid',
                        'Mileage' => '19,200 km',
                        'Condition' => 'Foreign Used',
                    ],
                    'features' => ['All-Wheel Drive', 'Backup Camera', 'Apple CarPlay', 'Alloy Wheels', 'Blind Spot Monitor']
                ];
            }
            $text = json_encode($payload, JSON_PRETTY_PRINT);
        } elseif (str_contains($promptLower, 'role: super_admin') || str_contains($promptLower, 'super admin')) {
            $text = "Zacma SaaS Director AI: System is operating normally across all organizations. We currently monitor multi-tenant activity, active subscription tiers, and marketplace payment volumes. You can configure dynamic categories, adjust subscription quotas, or audit security logs at any time from your control panel.";
        } elseif (str_contains($promptLower, 'role: customer') || str_contains($promptLower, 'buyer')) {
            $text = "Welcome to Zacma Concierge! I'm here to assist your shopping experience. You can search certified vehicles, luxury real estate, and high-grade electronics. If you find an item you like, you can directly book an inspection appointment or place a secure reservation deposit via Telebirr, SantimPay, or Chapa.";
        } elseif (str_contains($promptLower, 'hot lead') || str_contains($promptLower, 'score')) {
            $text = "Based on our interaction analysis: Customer exhibits high purchase intent with 4 recent vehicle inquiries, requested an in-person viewing, and responded within 2 hours. Recommended Action: Schedule immediate call and send quotation.";
        } elseif (str_contains($promptLower, 'summary') || str_contains($promptLower, 'summarize')) {
            $text = "Customer Summary: The client has active interest in listings, has viewed 6 properties/vehicles, scheduled 1 appointment, and has an ongoing negotiation deal. No follow-up in the last 48 hours.";
        } elseif (str_contains($promptLower, 'sms') || str_contains($promptLower, 'draft sms')) {
            $text = "Hello! Thank you for your interest in our listings at Zacma. We have prepared the details you requested. Would tomorrow 10:00 AM work for a quick viewing/call? Best regards, Sales Team.";
        } elseif (str_contains($promptLower, 'email') || str_contains($promptLower, 'draft email')) {
            $text = "Dear Customer,\n\nThank you for reaching out to us. We have updated our latest pricing and options tailored to your preferences. Please let us know when you would like to arrange an appointment or review financing options.\n\nWarm regards,\nZacma Dealership Team";
        } elseif (str_contains($promptLower, 'listing') || str_contains($promptLower, 'description')) {
            $text = "Exceptional opportunity! Pristine condition, meticulously maintained with full service history. Features modern specifications, premium comfort, and superior performance. Schedule your inspection today.";
        } else {
            $text = "Zacma AI Assistant: Ready to assist with your CRM, inventory management, customer inquiries, and sales pipeline operations. How can I help optimize your workflow today?";
        }

        return [
            'success' => true,
            'text' => $text,
            'tokens' => (int)(strlen($prompt . $text) / 4),
            'raw' => ['mode' => 'intelligent_mock'],
        ];
    }
}
