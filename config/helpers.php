<?php
/**
 * Helper functions for GroceryGo
 */

// Function to generate consistent colors for category badges
if (!function_exists('generateCategoryColor')) {
    function generateCategoryColor($categoryName) {
        // Hash the category name to generate a consistent value
        $hash = crc32($categoryName);
        
        // Define an array of visually distinct colors (background, text)
        $colors = [
            ['#f11919', '#000000'], // Red, dark text
            ['#2de933', '#fff'], // Green, white text
            ['#2699f7', '#fff'], // Light Blue, white text
            ['#e7410f', '#fff'], // Orange, white text
            ['#855240', '#fff'], // Brown, white text
            ['#26A69A', '#fff'], // Teal, white text
            ['#ffc917', '#000000'], // Yellow, dark text
            ['#465eda', '#fff'], // Indigo, white text
            ['#f31d64', '#fff'], // Pink, white text
            ['#3598e9', '#fff'], // Sky Blue, white text
            ['#85e616', '#fff'], // Light Green, white text
            ['#e7900d', '#050404'], // Amber, dark text
            ['#b125ca', '#fff'], // Purple, white text
            ['#0cb6a5', '#fff'], // Teal, white text
            ['#1fdd29', '#0c0b0b'], // Light green, dark text
        ];
        
        // Use the hash to select a color
        $index = abs($hash) % count($colors);
        
        return $colors[$index];
    }
}
