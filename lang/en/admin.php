<?php

return [
    'navigation' => [
        'groups' => [
            'access' => 'Access',
            'catalog' => 'Catalog',
            'sales' => 'Sales',
            'configuration' => 'Configuration',
        ],
        'resources' => [
            'users' => [
                'navigation' => 'Users',
                'singular' => 'User',
                'plural' => 'Users',
            ],
            'user_addresses' => [
                'navigation' => 'User Addresses',
                'singular' => 'User Address',
                'plural' => 'User Addresses',
            ],
            'stores' => [
                'navigation' => 'Store',
                'singular' => 'Store',
                'plural' => 'Stores',
            ],
            'product_categories' => [
                'navigation' => 'Product Categories',
                'singular' => 'Product Category',
                'plural' => 'Product Categories',
            ],
            'products' => [
                'navigation' => 'Products',
                'singular' => 'Product',
                'plural' => 'Products',
            ],
            'orders' => [
                'navigation' => 'Orders',
                'singular' => 'Order',
                'plural' => 'Orders',
            ],
            'system_settings' => [
                'navigation' => 'System Settings',
                'singular' => 'System Setting',
                'plural' => 'System Settings',
            ],
        ],
    ],

    'common' => [
        'not_available' => '-',
    ],

    'locale' => [
        'switch' => 'Switch language',
        'indonesian' => 'Bahasa Indonesia',
        'english' => 'English',
    ],

    'map' => [
        'search_placeholder' => 'Search address',
        'search_button' => 'Search',
        'searching' => 'Searching...',
        'choose_result' => 'Choose an address from the search results.',
        'no_results' => 'Address not found.',
        'search_failed' => 'Address search failed. Please try again.',
        'reverse_failed' => 'Failed to update address from map pin.',
        'selected' => 'Address selected.',
        'hint' => 'Click the map or drag the pin to fill coordinates. Address will be filled automatically.',
    ],

    'store' => [
        'fields' => [
            'logo' => 'Logo',
            'name' => 'Store name',
            'description' => 'Description',
            'phone_number' => 'Phone number',
            'open_time' => 'Open time (WIB)',
            'close_time' => 'Close time (WIB)',
            'service_radius_m' => 'Service radius (meter)',
            'base_delivery_fee' => 'Base delivery fee (IDR)',
            'address' => 'Address',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'location_map' => 'Location map',
            'service_radius_short' => 'Service radius (m)',
            'base_delivery_fee_short' => 'Base delivery fee',
        ],
        'helpers' => [
            'latitude_decimal' => 'Use a dot for decimals, example: -6.2',
            'longitude_decimal' => 'Use a dot for decimals, example: 106.8166667',
        ],
    ],

    'user_address' => [
        'fields' => [
            'user' => 'User',
            'label' => 'Address label',
            'recipient_name' => 'Recipient name',
            'phone_number' => 'Phone number',
            'address' => 'Address',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'location_map' => 'Location map',
            'address_note' => 'Address note',
            'postal_code' => 'Postal code',
            'is_default' => 'Default address',
            'is_active' => 'Active',
        ],
    ],

    'orders' => [
        'fields' => [
            'customer' => 'Customer',
            'address' => 'Address',
            'user' => 'User',
            'store' => 'Store',
            'user_address' => 'User address',
            'product' => 'Product',
            'order_date' => 'Order date',
            'cancel_reason' => 'Cancel reason',
        ],
        'filters' => [
            'order_date' => 'Order Date',
            'order_date_from' => 'From date',
            'order_date_until' => 'Until date',
        ],
        'actions' => [
            'group' => 'Update Status',
            'update_status' => 'Move to Next Status',
            'detail' => 'Detail',
        ],
        'messages' => [
            'update_status_heading' => 'Update order status',
            'update_status_description' => 'Status will be changed to :status.',
            'status_not_updatable' => 'Status cannot be updated',
            'payment_not_paid' => 'Payment status is not paid',
            'payment_required' => 'Order can only be processed after payment status is Paid.',
            'status_updated' => 'Order status updated',
            'status_updated_body' => 'Current status: :status',
            'status_update_failed' => 'Failed to update order status',
        ],
    ],

    'products' => [
        'fields' => [
            'category' => 'Category',
            'sku' => 'SKU',
            'weight' => 'Weight',
            'weight_unit' => 'Weight unit',
            'sort_order' => 'Sort order',
        ],
        'filters' => [
            'price_range' => 'Price Range',
            'price_min' => 'Minimum price',
            'price_max' => 'Maximum price',
        ],
    ],

    'product_categories' => [
        'fields' => [
            'icon' => 'Icon',
            'image' => 'Image',
        ],
    ],

    'users' => [
        'fields' => [
            'email' => 'Email address',
            'username' => 'Username',
            'is_email_verified' => 'Email verified',
            'role' => 'Role',
            'status' => 'Status',
            'password' => 'New password',
        ],
        'helpers' => [
            'email_already_verified' => 'This email is already verified.',
            'toggle_to_verify_email' => 'Turn this on to verify the user email.',
            'password_optional_on_edit' => 'Leave empty if you do not want to change password.',
        ],
    ],

    'system_settings' => [
        'fields' => [
            'group_name' => 'Group Name',
            'key_name' => 'Key Name',
            'label' => 'Label',
            'value' => 'Value',
            'type' => 'Type',
            'is_encrypted' => 'Encrypted',
            'is_active' => 'Active',
        ],
        'filters' => [
            'group_name' => 'Group',
        ],
        'types' => [
            'string' => 'String',
            'integer' => 'Integer',
            'boolean' => 'Boolean',
            'json' => 'JSON',
            'password' => 'Password',
        ],
    ],

    'integrations' => [
        'actions' => [
            'test_smtp' => 'Test SMTP',
            'test_fcm' => 'Test FCM',
        ],
        'fields' => [
            'smtp_to_email' => 'Recipient email',
            'fcm_device_token' => 'FCM device token',
            'fcm_title' => 'Notification title',
            'fcm_body' => 'Notification body',
        ],
        'helpers' => [
            'fcm_device_token' => 'Leave empty to validate config only without sending push.',
        ],
        'messages' => [
            'smtp_not_configured' => 'SMTP is not configured.',
            'smtp_target_required' => 'Recipient email is required.',
            'smtp_test_sent' => 'SMTP test email sent successfully.',
            'smtp_test_failed' => 'SMTP test failed.',
            'fcm_config_valid' => 'FCM configuration is valid.',
            'fcm_test_sent' => 'FCM test notification sent successfully.',
            'fcm_test_failed' => 'FCM test failed.',
        ],
    ],

    'enums' => [
        'user_role' => [
            'superuser' => 'Superuser',
            'admin' => 'Admin',
            'user' => 'User',
        ],
        'user_status' => [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'banned' => 'Banned',
        ],
        'order_status' => [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'packed' => 'Packed',
            'on_delivery' => 'On Delivery',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ],
        'payment_status' => [
            'unpaid' => 'Unpaid',
            'paid' => 'Paid',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
        ],
        'cart_status' => [
            'active' => 'Active',
            'checked_out' => 'Checked Out',
            'abandoned' => 'Abandoned',
        ],
        'fulfillment_type' => [
            'delivery' => 'Delivery',
            'pickup' => 'Pickup',
        ],
    ],

    'dashboard' => [
        'title' => 'Operations Dashboard',
        'subheading' => 'Monitor daily performance and process active orders from one place.',
        'quick_actions' => [
            'heading' => 'Order Quick Actions',
            'description' => 'Run order operational steps without opening each detail page.',
            'active_orders' => ':count active orders',
            'columns' => [
                'order' => 'Order',
                'customer' => 'Customer',
                'date' => 'Date',
                'total' => 'Total',
                'status' => 'Status',
                'payment' => 'Payment',
                'actions' => 'Actions',
            ],
            'empty' => 'There are no orders that need processing right now.',
            'total' => 'Total',
            'next_status' => 'Move to :status',
            'processing' => 'Processing...',
            'waiting_payment' => 'Waiting payment',
            'no_action' => 'No further action',
            'cancel' => 'Cancel',
            'detail' => 'Detail',
            'cancel_confirm' => 'Are you sure you want to cancel this order?',
            'cancel_modal_heading' => 'Cancel Order',
            'cancel_modal_description' => 'Fill in the reason for cancelling this order.',
            'cancel_modal_submit' => 'Save Cancellation',
            'cancel_reason_placeholder' => 'Example: Out of stock / customer request',
            'cancelled_note' => 'Cancelled from admin dashboard.',
            'status_not_allowed' => 'Status cannot be updated',
            'status_updated' => 'Order status updated',
            'status_updated_body' => 'Current status: :status',
            'update_failed' => 'Failed to update status',
            'cancel_blocked' => 'Order cannot be cancelled',
            'cancel_success' => 'Order cancelled',
            'cancel_failed' => 'Failed to cancel order',
        ],
        'trend' => [
            'heading' => '7-Day Order Trend',
            'description' => 'Comparison between incoming orders and paid orders.',
            'total_label' => 'Incoming Orders',
            'paid_label' => 'Paid Orders',
        ],
    ],

    'stats' => [
        'users' => 'Users',
        'users_description' => 'Total registered users',
        'categories' => 'Categories',
        'categories_description' => 'Active catalog groups',
        'products' => 'Products',
        'products_description' => 'Available product records',
        'pending_orders' => 'Pending Orders',
        'pending_orders_description' => 'Orders waiting for processing',
        'revenue_today' => 'Today\'s Revenue',
        'revenue_today_description' => 'Total paid orders today',
        'completed_orders' => 'Completed Orders',
        'completed_orders_description' => 'Total completed orders',
    ],
];
