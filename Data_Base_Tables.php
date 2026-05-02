//===========================================
// Module 1: Core (8 Tables)
//===========================================
Core_Mudule{
  agencies [icon: globe, color: blue] {
    id string [pk]
    agency_status_id string [ref: > agency_statuses.id]
    country_id string [ref: > countries.id]
    base_currency_id string [ref: > currencies.id]
    name string
    slug string [unique]
    logo_path string 
    brand_color string 
    contact_email string
    contact_phone string
    official_address text
    timezone string [default: 'UTC']
    date_format string [default: 'DD/MM/YYYY']
    social_links jsonb
    account_manager_id string
    is_active boolean [default: true]
    created_at timestamp
    deleted_at timestamp 
  }
  agency_statuses [icon: check-square, color: blue] {
    id string [pk]
    name_translations jsonb
    color_code string
    sort_order int
    is_active boolean [default: true]
  }
  accounts {
    id varchar [pk]
    email varchar [unique]
    password_hash varchar
    is_super_admin boolean [default: false]
    last_login_at timestamp
    created_at timestamp
  }
  users [icon: user-check, color: yellow] {
    id string [pk]
    agency_id string [ref: > agencies.id]
    email string [unique]
    password_hash string
    full_name string
    base_role string
    is_active boolean [default: true]
    created_at timestamp
    deleted_at timestamp 
  }
  staff_roles [icon: shield, color: orange] { 
    id string [pk] 
    agency_id string [ref: > agencies.id]
    name string 
    permissions jsonb 
    deleted_at timestamp 
  }
  staff_assignments [icon: user-plus, color: orange] { 
    id string [pk] 
    user_id string [ref: > users.id]
    role_id string [ref: > staff_roles.id]
  }
  audit_logs [icon: list, color: gray] {
    id string [pk]
    agency_id string [ref: > agencies.id]
    actor_id string // ID from users, vendor_users, or clients
    actor_type string // 'staff', 'vendor', 'client'
    action string 
    entity_type string 
    entity_id string
    old_values jsonb
    new_values jsonb
    ip_address string
    created_at timestamp
  }
  api_request_logs {
    id varchar [pk]
    agency_id varchar [ref: > agencies.id]
    actor_id varchar [ref: > accounts.id]
    method varchar // GET, POST, etc.
    url text
    payload jsonb // Request data
    response_body jsonb // Response data
    status_code int
    duration_ms float
    ip_address varchar
    user_agent text
    trace_id varchar [note: "Unique ID to link related logs"]
    created_at timestamp
  }
}

//===========================================
// Module 2: Geo (7 Tables)
//===========================================
Geo_Module{
  countries [icon: flag, color: green] {
    id string [pk]
    iso_code string [unique]
    emoji_flag string
    name_translations jsonb
  }
  cities [icon: map-pin, color: green] {
    id string [pk]
    agency_id string [ref: > agencies.id]
    country_id string [ref: > countries.id]
    name_translations string
    slug string
    timezone string
    latitude decimal
    longitude decimal
    meta jsonb 
    created_at timestamp
    deleted_at timestamp
  }
  destinations [icon: map, color: green] {
    id string [pk]
    agency_id string [ref: > agencies.id]
    country_id string [ref: > countries.id]
    slug string
    name_translations jsonb
    description_translations jsonb
    map_data jsonb
    geojson jsonb
    tourism_info jsonb
    regional_data jsonb
    country_code string
    view_count int [default: 0]
    is_active boolean [default: true]
    created_at timestamp
    updated_at timestamp
    deleted_at timestamp 
  }
  agency_destination {
    id varchar [pk]
    agency_id varchar [ref: > agencies.id]
    destination_id varchar [ref: > destinations.id]
    is_featured boolean [default: false] // Unique to that agency's view
    custom_price_modifier decimal        // Example: Agency-specific markup
    is_active boolean [default: true]
    created_at timestamp
  }
  languages [icon: languages, color: purple] {
    id string [pk]
    code string [unique] 
    name string
    native_name string
    is_rtl boolean [default: false]
  }
  agency_languages [icon: settings, color: blue] {
    id string [pk]
    agency_id string [ref: > agencies.id]
    language_id string [ref: > languages.id]
    is_default boolean [default: false]
    is_active boolean [default: true]
  }
  currencies [icon: dollar-sign, color: purple] {
    id string [pk]
    code string [unique] 
    symbol string
    name_translations jsonb
    decimal_places int [default: 2]
  }
}

//===========================================
// Module 3: Master ( 4 Tables)
//===========================================
Master_Module{

  transportation_types [icon: truck, color: orange]{
    id string pk
    agency_id string
    name string
    capacity_pax int
    description jsonb
    created_at timestamp
  }
  
  hotel_labels [icon: tag, color: pink] {
    id string [pk]
    agency_id string [ref: > agencies.id]
    name_translations jsonb // e.g., {"en": "Eco-Friendly", "ar": "صديق للبيئة"}
    color_code string // Hex code for UI display
    icon_class string 
    is_active boolean [default: true]
  }
  //tour Amenities
  amenities_master [icon: check-circle, color: blue] {
    id uuid pk
    agency_id string [ref: > agencies.id]
    icon_class string // FontAwesome or custom icon key
    name_translations jsonb // {"en": "Free WiFi", "ar": "واي فاي مجاني"}
    category string // e.g., 'Transportation', 'Catering', 'Health & Safety'
    is_active boolean [default: true]
  }

  offer_labels_master [icon: tag, color: gray] {
    id uuid pk
    agency_id string [ref: > agencies.id]
    name_translations jsonb // e.g., {"en": "Gold Plan", "ar": "الباقة الذهبية"}
    color_code string // For UI styling (e.g., #FFD700)
    is_active boolean [default: true]
    created_at timestamp
  }
}

//===========================================
// Module 4: Day_Tours (2 Tables)
//===========================================
Day_Tour_Module{
  day_tours {
    id ulid [pk, note: 'Sortable distributed unique ID']
    agency_id uuid [not null]
    city_id integer [not null]
    destination_id integer [not null]
    
    // JSONB fields for multi-language support
    title jsonb [not null, note: '{"en": "...", "ar": "..."}']
    description jsonb [not null]
    
    is_active boolean [default: true]
    is_shared boolean [default: false, note: 'B2B sharing flag']
    
    created_at timestamp [default: `now()`]
    updated_at timestamp [default: `now()`]
  }
  day_tour_images {
    id serial [pk]
    day_tour_id ulid [ref: > day_tours.id] // Many-to-one relationship
    path varchar(255) [not null, note: 'CDN/S3 URL']
    is_primary boolean [default: false]
    sort_order integer [default: 0]
    created_at timestamp [default: `now()`]
  }
}

//===========================================
// Module 5: Drivers (4 Tables)
//===========================================
Driver_Module{
  driver_profiles [icon: user, color: orange] {
    id string [pk]
    agency_id string //[ref: > agencies.id]
    user_id string //[ref: > users.id]
    city_id string //[ref: > cities.id]
    destination_id integer [not null]
    profile_photo_path
    mobile_number varchar
    emergency_contact_number varchar
    passport_number varchar
    passport_copy_path text // Stores the path to the uploaded file
    bio_translations jsonb
    languages jsonb
    years_of_experience int
    current_rate_per_day decimal
    availability_status string
    is_active boolean [default: true]
    note text
    created_at timestamp
  }
  vehicles{
    id string pk
    agency_id string fk
    driver_id string fk
    trans_type_id string fk
    make string
    model string
    manufacture_year int
    license_plate string [unique]
    color string
    amenities jsonb
    is_active boolean [default: true]
    created_at timestamp
  }
  vehicle_photos{
    id serial [pk]
    vehicle_id ulid [ref: > vehicle.id] // Many-to-one relationship
    path varchar(255) [not null, note: 'CDN/S3 URL']
    is_primary boolean [default: false]
    sort_order integer [default: 0]
    created_at timestamp [default: `now()`]
  }
  driver_compliance [icon: shield, color: red] {
    id string pk
    driver_id string fk
    document_type string
    document_number string
    expiry_date date
    file_path string
    verification_status string
    updated_at timestamp
  }
}

//===========================================
// Module 6: Tourguides (2 Tables)
//===========================================
Tourguide_Module{
  guide_profiles [icon: map, color: green] {
    id string [pk]
    agency_id string //[ref: > agencies.id]
    user_id string //[ref: > users.id]
    city_id string //[ref: > cities.id]
    destination_id integer [not null]
    profile_photo_path
    mobile_number varchar
    emergency_contact_number varchar
    passport_number varchar
    passport_copy_path text // Path to file storage (S3/Local)
    bio_translations jsonb
    languages jsonb
    specialties jsonb
    years_of_experience int
    current_rate_per_day decimal
    has_own_equipment boolean [default: false]
    equipment_details text
    availability_status string
    is_active boolean [default: true]
    note text
    created_at timestamp
  }
  guide_compliance [icon: shield, color: red] {
    id string pk
    guide_id string fk
    document_type string
    document_number string
    expiry_date date
    file_path string
    verification_status string
    updated_at timestamp
  }
}



//===========================================
// Module 7: Accommodations (18 Tables)
//===========================================
Accommodation_module{
  accommodation_types [icon: list, color: blue]{
    id string [pk]
    agency_id string [ref: > agencies.id]
    name_translations jsonb
    slug string
    is_active boolean [default: true]
    created_at timestamp
  }
  hotel_amenities_master [icon: list, color: gray] {
    id string [pk]
    category string
    name_translations jsonb
    icon_class string
  }
  hotel_profiles [icon: home, color: blue]{
    id string [pk]
    agency_id string [ref: > agencies.id]
    // Links to vendor_users in Module 01
    vendor_user_id string [ref: - vendor_users.id] 
    city_id string [ref: > cities.id]
    accommodation_type_id string [ref: > accommodation_types.id]
    name string
    star_rating int
    base_currency_id string [ref: > currencies.id]
    contract_type string // 'Static', 'Dynamic', '3rdParty'
    markup_percent decimal [default: 0]
    reservation_email string
    website_url string
    video_url string 
    phone string
    check_in_time string [default: '14:00']
    check_out_time string [default: '12:00']
    address text
    latitude decimal
    longitude decimal
    policy_translations jsonb
    note text 
    is_active boolean [default: true]
    created_at timestamp
    deleted_at timestamp
  }
  hotel_label_pivot [icon: link, color: pink] {
    hotel_id string [ref: > hotel_profiles.id]
    label_id string [ref: > hotel_labels.id]
  }
  hotel_offer_label_pivot [icon: link, color: pink] {
    hotel_id string [ref: > hotel_profiles.id]
    offer_label_id string [ref: > offer_labels.id]
  }
  room_categories [icon: layout, color: blue]{
    id string [pk]
    hotel_id string [ref: > hotel_profiles.id]
    name_translations jsonb 
    sq_meters int
    room_view_type string
    is_smoking boolean [default: false]
    base_capacity_pax int
    max_extra_beds int
    bed_type string 
    amenities jsonb
    total_rooms_count int
    is_active boolean [default: true]
    deleted_at timestamp
  }
  room_inventory [icon: table, color: cyan] {
    id string [pk]
    room_category_id string [ref: > room_categories.id]
    date date
    total_rooms int
    allotted_rooms int
    booked_rooms int [default: 0]
    price_per_night_net decimal
    price_per_night_sell decimal
    is_stopped boolean [default: false]
  }
  hotel_contracts [icon: file-text, color: blue] {
    id string [pk]
    agency_id string [ref: > agencies.id]
    hotel_id string [ref: > hotel_profiles.id]
    start_date date
    end_date date
    currency_id string [ref: > currencies.id]
    is_active boolean [default: true]
    cancellation_policy_translations jsonb
  }
  hotel_amenity_pivot [icon: link, color: gray] {
    hotel_id string [ref: > hotel_profiles.id]
    amenity_id string [ref: > hotel_amenities_master.id]
  }
  hotel_images [icon: image, color: gray] { 
    id string [pk] 
    hotel_id string [ref: > hotel_profiles.id]
    image_url string 
    is_primary boolean [default: false]
    sort_order int
  }
  hotel_reviews [icon: star, color: yellow] { 
    id string [pk] 
    hotel_id string [ref: > hotel_profiles.id]
    client_id string [ref: > clients.id] // Links to travelers from Module 01
    rating int 
    comment text 
    created_at timestamp
  }
  hotel_translations [icon: home, color: blue] {
    id string pk
    hotel_id string fk
    language_id string fk
    description text
    policy_info text
  }
  meal_plans [icon: coffee, color: brown] {
    id string [pk]
    agency_id string [ref: > agencies.id]
    name string
    description_translations jsonb
  }
  hotel_meal_plan_prices [icon: dollar-sign, color: green] {
    id string [pk]
    hotel_id string [ref: > hotel_profiles.id]
    meal_plan_id string [ref: > meal_plans.id]
    price_per_pax decimal
    child_price_per_pax decimal
  }
  hotel_child_policies [icon: user, color: pink] {
    id string [pk]
    hotel_id string [ref: > hotel_profiles.id]
    max_child_age int
    free_child_limit int
    extra_bed_charge decimal
    created_at timestamp
  }
  seasonal_pricing [icon: trending-up, color: green]{
    id string [pk]
    room_category_id string [ref: > room_categories.id]
    season_name string
    start_date date
    end_date date
    price_per_night decimal
    currency_id string [ref: > currencies.id]
    min_stay_nights int [default: 1]
    is_active boolean [default: true]
  }
  hotel_price_modifiers [icon: trending-up, color: red] {
    id string [pk]
    hotel_id string [ref: > hotel_profiles.id]
    room_category_id string [ref: > room_categories.id]
    modifier_type string // 'EARLY_BIRD', 'LAST_MINUTE', 'STAY_PAY'
    discount_percent decimal
    min_days_advance int
    min_nights int
    valid_from date
    valid_to date
  }
  hotel_extra_services [icon: plus-circle, color: blue] {
    id string [pk]
    hotel_id string [ref: > hotel_profiles.id]
    name_translations jsonb
    price_type string // 'PER_PAX', 'PER_ROOM', 'PER_STAY'
    base_price decimal
    is_active boolean [default: true]
  }
}


//===========================================
// Module 8: Activities (3 Tables)
//===========================================
activitiy_Module{
  activities [icon: plus-circle, color: purple] {
    id uuid pk
    agency_id string [ref: > agencies.id]
    destination_id string [ref: > destinations.id]
    city_id string [ref: > cities.id]
    image_path string
    is_shareable boolean [default: false] // 'share' from old schema
    is_multiple boolean [default: false]  // 'multy' from old schema
    is_active boolean [default: true]
    created_at timestamp
    updated_at timestamp
  }

  activity_translations [icon: language, color: purple] {
    id uuid pk
    service_id uuid [ref: > services_master.id]
    language_id string [ref: > languages.id]
    title string
    description text
    note text
  }

  activity_pricing [icon: dollar-sign, color: green] {
    id uuid pk
    service_id uuid [ref: > services_master.id]
    currency_id string 
    net_price decimal // Cost for the agency
    sell_price decimal // Price for the customer
    season_type string // 'high', 'low', 'peak'
    valid_from date
    valid_to date
  }
}


//===========================================
// Module 9: Leads CRM (12 Tables)
//===========================================
Leads_module {
  lead_sources [icon: share-2, color: blue] {
    id string pk
    agency_id string fk
    name string
    utm_source string
    is_active boolean [default: true]
  }
  lead_statuses [icon: check-circle, color: orange] {
    id string pk
    agency_id string fk
    name_translations jsonb
    color_code string
    is_closing_status boolean [default: false]
    sort_order int
  }
  leads [icon: user-plus, color: orange] {
    id string pk
    agency_id string fk
    source_id string fk
    status_id string fk
    assigned_to string fk
    full_name string
    email string
    phone_number string
    nationality_code string
    language_id string fk
    lead_score int [default: 0]
    is_Archived
    last_activity_at timestamp
    created_by
    created_at timestamp
  }
  lead_requirements [icon: search, color: purple] {
    id string pk
    lead_id string fk
    destination_id string fk
    pax_adults int
    pax_children int
    preferred_month int
    travel_date_start date
    travel_date_end date
    budget_range_min decimal
    budget_range_max decimal
    currency string
    needs_accommodation boolean
    needs_flights boolean
    needs_tours boolean
    travel_style string
    notes text
  }
  lead_activities [icon: clock, color: gray] {
    id string pk
    lead_id string fk
    user_id string fk
    type string
    description text
    metadata jsonb
    created_at timestamp
  }
  lead_reminders [icon: bell, color: red] {
    id string pk
    lead_id string fk
    user_id string fk
    reminder_text text
    remind_at timestamp
    is_completed boolean [default: false]
  }
  lost_reasons [icon: x-circle, color: red] {
    id string pk
    agency_id string fk
    reason_translations jsonb
  }
  lead_conversions [icon: trophy, color: yellow] {
    id string pk
    lead_id string fk
    booking_id string fk
    converted_by string fk
    conversion_value decimal
    lost_reason_id string fk
    created_at timestamp
  }
  lead_notes { 
    id string pk 
    lead_id string 
    user_id string 
    note text 
  }
  lead_attachments { 
    id string pk 
    lead_id string 
    file_path string 
  }
  lead_requests [icon: send, color: blue] {
    id string pk
    lead_id string [ref: > leads.id] // The original lead
    sender_agency_id string [ref: > agencies.id]
    receiver_agency_id string [ref: > agencies.id] // Target company
    
    status string [note: 'pending, accepted, rejected, cancelled']
    commission_type string [note: 'fixed, percentage']
    commission_value decimal
    
    shared_requirements_id string [ref: > lead_requirements.id]
    expires_at timestamp
    created_at timestamp
  }
  agency_connections [icon: link, color: green] {
    id string pk
    requester_agency_id string [ref: > agencies.id]
    provider_agency_id string [ref: > agencies.id]
    status string [note: 'connected, blocked']
    agreement_details jsonb // Store terms of cooperation
  }
}


//===========================================
// Module 10: Pricing (6 Tables)
//===========================================
Pricing_Module{
  services {
    id ulid [pk]
    agency_id ulid [ref: > agencies.id]
    
    // Instead of a specific ID, we categorize the service type
    service_type varchar [note: "e.g., 'PRIVATE_DRIVER', 'HOTEL_ROOM', 'FLIGHT'"]
    service_class varchar [note: "e.g., 'ECONOMY', 'LUXURY', '5_STAR'"]
    
    title_translations jsonb
    status varchar [default: 'active']
  }
  price_season_definitions [icon: calendar, color: purple] {
    id ulid [pk]
    agency_id ulid [ref: > agencies.id]
    destination_id ulid [ref: > destinations.id]
    name varchar
    start_date date
    end_date date
    is_recurring boolean [note: "If true, repeats annually"]
  }
  pricing_engine [icon: dollar-sign, color: orange]{
    id ulid [pk]
    agency_id ulid [ref: > agencies.id]
    service_id ulid [ref: > services.id, note: "Links to Tour, Car, or Driver"]
    season_id ulid [ref: > season_definitions.id, null]
    destination_id ulid [ref: > destinations.id]
    
    // Logic Ranges
    min_pax smallint [default: 1]
    max_pax smallint [default: 99]
    min_days smallint [default: 1]
    max_days smallint [default: 365]
    
    // Financials
    operator_net_cost decimal(12,2) [note: "The 'Buying' price"]
    agency_profit_margin decimal(12,2) [note: "Fixed profit added to net"]
    markup_percentage decimal(5,2) [default: 0]
    currency varchar(3) [default: "USD"]
    
    // Status
    is_active boolean [default: true]
    created_at timestamp
    updated_at timestamp
  }
  sales_commissions [icon: dollar-sign, color: yellow] {
    id ulid [pk]
    agency_id ulid [ref: > agencies.id]
    user_id ulid [ref: > users.id, note: "The employee/agent"]
    target_type varchar [note: "fixed or percentage_of_profit"]
    commission_value decimal(12,2)
    is_active boolean [default: true]
    created_at timestamp
  }
  transportation_rates [icon: truck, color: orange] {
    id uuid pk
    agency_id string [ref: > agencies.id]
    destination_id string [ref: > destinations.id] // Fuel/Taxes vary by city/country
    trans_type_id string [ref: > transportation_types.id]
    rate_type string // 'per_km', 'per_day', 'transfer'
    net_cost decimal
    season_type string
    start_date date
    end_date date
    is_active boolean
  }

  guide_rates [icon: user, color: green] {
    id uuid pk
    agency_id string [ref: > agencies.id]
    destination_id string [ref: > destinations.id] // Different countries have different labor rates
    guide_id string [ref: > guide_profiles.id]
    daily_rate_net decimal
    season_type string
    start_date date
    end_date date
    valid_from date
    valid_to date
  }

}

//===========================================
// Module 11: Packages (4 Tables)
//===========================================
Packages_Module{
  packages [icon: package, color: green] {
    id uuid pk
    agency_id string [ref: > agencies.id]
    destination_id string [ref: > destinations.id]
    featured_image_url string 
    banner_image_url string  
    slug string [unique]
    duration_days int
    duration_nights int
    map_coordinates text // Geo-coordinates or Embed link
    total_rating decimal [default: 0]
    total_reviews int [default: 0]
    is_active boolean [default: true]
    sort_order int [default: 0]
    created_at timestamp
    updated_at timestamp
  }

  package_translations [icon: language, color: green] {
    id uuid pk
    package_id uuid [ref: > tour_packages.id]
    language_id string [ref: > languages.id]
    title string // e.g., "Tour 6 Days - 5 Nights"
    description text
    meta_title string
    meta_description text
  }

  package_media [icon: video, color: green] {
    id uuid pk
    package_id uuid [ref: > tour_packages.id]
    media_type string // 'photo', 'video_link', '360_view'
    url string
    sort_order int
  }

  package_faqs [icon: help-circle, color: green] {
    id uuid pk
    package_id uuid [ref: > tour_packages.id]
    question_translations jsonb
    answer_translations jsonb
    is_active boolean [default: true]
    sort_order int
  }
}



//===========================================
// Module 12: Offers ( 6 Tables)
//===========================================
Offers_Module{
  
  offers {
    id uuid [pk]
    agency_id string [ref: > agencies.id]
    lead_id string [ref: > leads.id]
    destination_id string [ref: > destinations.id]
    title string
    currency_id string 
    status string [note: 'draft, sent, accepted, rejected']
    duration_days int
    
    // PERFORMANCE CACHE: Mirror the selected label's prices here
    // These allow for instant dashboard loading and reporting
    cached_total_net_cost decimal [default: 0]
    cached_total_markup decimal [default: 0]
    cached_grand_total decimal [default: 0]
    
    expiration_date timestamp
    created_by 
    created_at timestamp
    updated_at timestamp

    Note: "The parent container. Caches totals from the selected label for high speed."
  }

  offer_labels {
    id uuid [pk]
    agency_id string [ref: > agencies.id]
    offer_id uuid [ref: > offers.id]
    label_master_id uuid // e.g., 'Silver', 'Gold', 'Platinum'
    
    total_net_cost decimal [default: 0]  // Auto-calculated from offer_items
    added_profit decimal [default: 0]    // Manual agency markup per tier
    final_price decimal [default: 0]     // (total_net_cost + added_profit)
    
    is_selected boolean [default: false] // The version the customer chose
    order_column int [default: 0]
    
    Note: "Allows for multiple pricing options within a single offer."
  }

  offer_days {
    id uuid [pk]
    agency_id string [ref: > agencies.id]
    offer_label_id uuid [ref: > offer_labels.id]
    day_number int
    date
    city_id string [ref: > cities.id]
    day_tour_id 

    description_override text
    
    Note: "Specific per-day itinerary per price tier."
  }

  offer_items {
    id uuid [pk]
    agency_id string [ref: > agencies.id]
    offer_label_id uuid [ref: > offer_labels.id]
    offer_day_id uuid [ref: > offer_days.id, null] // Nullable for global items
    
    // Polymorphic Connection
    itemable_type string // 'Hotel', 'Flight', 'Driver', 'Service'
    itemable_id uuid 
    
    item_name_snapshot string // Crucial: Store name even if master record changes
    quantity int [default: 1]
    unit_net_cost decimal
    total_net_cost decimal // (quantity * unit_net_cost)
    
    is_global boolean [default: false] // True if it's an offer-wide item (e.g. Flights)
    details_json jsonb // Snapshot of the item features
    order_column int
    
    Note: "The individual units of cost. Can be linked to a day or the whole offer."
  }

  bookings {
    id uuid [pk]
    agency_id string [ref: > agencies.id]
    offer_id uuid [ref: > offers.id] // The source offer
    offer_label_id uuid [ref: > offer_labels.id] // The specific tier chosen
    destination_id string [ref: > destinations.id]
    operated_by string [ref: > users.id] // The agent handling the booking

    lead_id string [ref: > leads.id]
    customer_name_snapshot string
    customer_email_snapshot string
    
    total_net_cost decimal
    total_markup decimal
    grand_total decimal
    currency_id string
    
    status varchar [note: 'confirmed, pending_payment, completed, cancelled']
    departure_date date
    created_at timestamp
    
    Note: "The financial 'Source of Truth'. Created when an Offer Label is accepted."
  }

  
  booking_vouchers {
      id uuid [pk]
      agency_id string [ref: > agencies.id]
      booking_id uuid [ref: > bookings.id]
      offer_item_id uuid [ref: > offer_items.id]
      
      // Unified Provider Info
      provider_type varchar // 'Hotel', 'Driver', 'Guide', 'Airline'
      provider_id uuid [ref: > resources.id] // Points to the specific Driver, Guide, or Hotel
      
      // Consolidated Logistics (Previously in Assignments)
      location_name varchar // Pickup location (Driver) or Meeting point (Guide)
      scheduled_time timestamp // Pickup time or Tour start time
      operational_notes text // Specific instructions for the supplier
      
      // Status & Communication Tracking
      confirmation_number varchar
      reservation_status varchar [note: 'requested, confirmed, rejected, cancelled']
      last_email_sent_at timestamp
      email_token uuid [unique] // For one-click supplier confirmation
      
      created_at timestamp
      updated_at timestamp

      Note: "One table to rule them all. Handles all logistics and supplier comms."

  }
}

//===========================================
// Module 13: Group_Offers ( 9 Tables)
//===========================================
group_offers_Module{
 // 1. THE GROUP TRIP (Master Anchor)
  group_offers {
    id uuid [pk]
    agency_id string [ref: > agencies.id]
    tour_package_id uuid [ref: > tour_packages.id]
    offer_id
    departure_date date
    return_date date
    
    max_capacity int [default: 50]
    remaining_capacity int // Total global seats left
    
    status varchar [note: 'open, guaranteed, full, cancelled']
    is_active boolean [default: true]
    created_at timestamp

    Note: "The central bus/trip for all agents to sell into."
  }

  // 2. THE MULTI-AGENT QUOTA (B2B Allotment)
  group_agent_allotments {
    id uuid [pk]
    group_offer_id uuid [ref: > group_offers.id]
    partner_agency_id uuid [ref: > agencies.id] // The sub-agent/partner
    
    allocated_seats int [default: 0] // Seats blocked for this agent
    booked_seats int [default: 0]
    
    // High-performance feature: The Release
    release_date timestamp [note: "Unsold seats return to global pool after this date"]
    
    net_rate_override decimal(12,2) [note: "Special cost for this specific agent"]
    is_exclusive boolean [default: false] // If true, these seats CANNOT be sold by others
    
    Note: "Manages B2B blocks. Prevents Agent A from taking Agent B's seats."
  }

  // 3. THE OCCUPANCY PRICING
  group_price_tiers {
    id uuid [pk]
    group_offer_id uuid [ref: > group_offers.id]
    
    occupancy_type varchar [note: 'single, double, triple']
    net_cost_per_pax decimal(12,2)
    sell_price_per_pax decimal(12,2)
    
    Note: "The base prices for the tour."
  }

  // 4. THE RESERVATIONS (Sales)
  group_reservations {
    id uuid [pk]
    agency_id uuid [ref: > agencies.id] // Who made the booking (Owner or Partner)
    group_offer_id uuid [ref: > group_offers.id]
    agent_allotment_id uuid [ref: > group_agent_allotments.id, null] // Links to the quota used
    lead_id string [ref: > leads.id]
    
    total_pax int
    total_amount decimal(12,2)
    status varchar [note: 'pending, confirmed, cancelled']
  }

  // 5. THE MANIFEST (Who is in which room?)
  group_reservation_occupancy {
    id uuid [pk]
    reservation_id uuid [ref: > group_reservations.id]
    passenger_id uuid [ref: > booking_passengers.id]
    price_tier_id uuid [ref: > group_price_tiers.id]
    room_number_assigned varchar
  }

  // 6. THE INVENTORY SAFETY NET
  group_agency_allotment_calendar {
    id uuid [pk]
    agency_id string [ref: > agencies.id]
    resource_type varchar [note: 'bus_seat, hotel_room']
    resource_id string
    event_date date
    total_allotment int
    used_allotment int
  }

  // 7. THE AUDIT LOG
  group_manifest_logs {
    id uuid [pk]
    group_offer_id uuid [ref: > group_offers.id]
    user_id uuid [ref: > users.id]
    action_type varchar
    details jsonb
    created_at timestamp
  }
  // 8. THE PHYSICAL RESOURCES
  // This table defines the "What" (The specific Bus or the specific Hotel Room type)
  Table resources {
    id uuid [pk]
    agency_id uuid [ref: > agencies.id]
    resource_type varchar // 'vehicle', 'accommodation', 'guide'
    name varchar // e.g., 'Mercedes Sprinter - Plate XYZ-123' or 'Double Deluxe Room'
    
    base_capacity int [default: 1]
    provider_id uuid [note: "Link to the Operator/Supplier profile"]
    
    is_active boolean [default: true]
    
    Note: "The real-world assets you are selling."
  }

  // 9. THE TRAVELERS (The People)
  Table booking_passengers {
    id uuid [pk]
    agency_id uuid [ref: > agencies.id]
    lead_id uuid [ref: > leads.id] // Connects back to the main customer
    
    first_name varchar
    last_name varchar
    gender varchar
    date_of_birth date
    
    passport_number varchar
    passport_expiry date
    nationality_id int
    
    special_requirements text // Allergies, wheelchair, etc.
    
    created_at timestamp
    
    Note: "Detailed profile for every person on the trip."
  }
}


//===========================================
// Module 14: Finance  ( 23 Tables)
//===========================================




//===========================================
// Module 15: Communication & WhatsApp ( 10 Tables)
//===========================================


//===========================================
// Module 16: Notifications & Alerts Module ( 6 Tables)
//===========================================


//===========================================
// Module 17: Content Management (CMS) & Blog ( 18 Tables)
//===========================================
website_cms_pages {
  blog_posts (
      id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
      agency_id UUID NOT NULL,
      category_id UUID REFERENCES blog_categories(id),
      author_id UUID NOT NULL,
      featured_image_id UUID, -- References a media table
      status VARCHAR(20) DEFAULT 'draft', -- draft, scheduled, published, archived
      is_featured BOOLEAN DEFAULT false,
      view_count INT DEFAULT 0,
      published_at TIMESTAMP WITH TIME ZONE,
      created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
      updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
      deleted_at TIMESTAMP WITH TIME ZONE -- Soft delete
  );

  blog_post_translations (
      id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
      post_id UUID REFERENCES blog_posts(id) ON DELETE CASCADE,
      language_code VARCHAR(5) NOT NULL, -- e.g., 'en', 'fr', 'es'
      title TEXT NOT NULL,
      slug TEXT NOT NULL,
      summary TEXT,
      content TEXT,
      seo_title TEXT,
      seo_description TEXT,
      UNIQUE(post_id, language_code),
      UNIQUE(language_code, slug)
  );

  blog_categories (
      id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
      agency_id UUID NOT NULL,
      parent_id UUID REFERENCES blog_categories(id), -- For sub-categories
      is_active BOOLEAN DEFAULT true,
      sort_order INT DEFAULT 0,
      name_json JSONB, -- Lightweight name translations
      slug_json JSONB
  );

  blog_tags (
      id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
      agency_id UUID NOT NULL,
      name_json JSONB,
      slug VARCHAR(255) NOT NULL,
      UNIQUE(agency_id, slug)
  );

  blog_post_tag_pivot (
      post_id UUID REFERENCES blog_posts(id) ON DELETE CASCADE,
      tag_id UUID REFERENCES blog_tags(id) ON DELETE CASCADE,
      PRIMARY KEY (post_id, tag_id)
  );

  blog_comments (
      id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
      post_id UUID REFERENCES blog_posts(id) ON DELETE CASCADE,
      user_id UUID, -- Null if guest
      parent_id UUID REFERENCES blog_comments(id), -- For nesting
      guest_name VARCHAR(100),
      guest_email VARCHAR(255),
      comment_body TEXT NOT NULL,
      status VARCHAR(20) DEFAULT 'pending', -- pending, approved, spam, deleted
      ip_address INET,
      user_agent TEXT,
      created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
  );

  blog_post_related (
      post_id UUID REFERENCES blog_posts(id) ON DELETE CASCADE,
      related_post_id UUID REFERENCES blog_posts(id) ON DELETE CASCADE,
      PRIMARY KEY (post_id, related_post_id)
  );

  blog_settings (
      agency_id UUID PRIMARY KEY,
      blog_name_json JSONB,
      posts_per_page INT DEFAULT 10,
      allow_comments BOOLEAN DEFAULT true,
      require_approval BOOLEAN DEFAULT true,
      og_default_image UUID,
      scripts_header TEXT, -- For Google Analytics/Ads
      scripts_footer TEXT
  );

  pages (
      id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
      agency_id UUID NOT NULL,
      template_name VARCHAR(100) DEFAULT 'default',
      status VARCHAR(20) DEFAULT 'published',
      last_updated_by UUID,
      created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
  );

  page_translations (
      id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
      page_id UUID REFERENCES pages(id) ON DELETE CASCADE,
      language_code VARCHAR(5) NOT NULL,
      title TEXT NOT NULL,
      slug TEXT NOT NULL,
      content JSONB, -- Structure for block-based editors
      seo_meta JSONB,
      UNIQUE(language_code, slug)
  );

  website_menus [icon: menu, color: cyan] {
    id string pk
    agency_id string fk
    parent_id string fk 
    title_translations jsonb
    link_url string
    sort_order int
  }

  website_sections [icon: layout, color: purple] {
    id string pk
    page_id string fk
    type string
    sort_order int
    config jsonb
  }

  settings [icon: settings, color: gray]{
    id string pk
    agency_id string
    key string
    value jsonb
    group string
    updated_at timestamp
  }

   media_assets (
      id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
      agency_id UUID NOT NULL,
      folder_id UUID REFERENCES media_folders(id) ON DELETE SET NULL,
      file_name TEXT NOT NULL,
      file_url TEXT NOT NULL,
      file_type VARCHAR(50), -- image/jpeg, application/pdf, etc.
      file_size INT, -- In bytes
      alt_text_translations JSONB,
      dimensions JSONB, -- { "width": 1920, "height": 1080 }
      created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
  );

  media_folders (
      id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
      agency_id UUID NOT NULL,
      parent_id UUID REFERENCES media_folders(id),
      name TEXT NOT NULL
  );
 
  website_redirects (
      id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
      agency_id UUID NOT NULL,
      old_path TEXT NOT NULL, -- e.g., /old-blog-post
      new_path TEXT NOT NULL, -- e.g., /new-blog-post
      status_code INT DEFAULT 301, -- 301 (Permanent) or 302 (Temporary)
      is_active BOOLEAN DEFAULT true,
      created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
      UNIQUE(agency_id, old_path)
  );

  forms (
      id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
      agency_id UUID NOT NULL,
      name TEXT NOT NULL,
      schema JSONB, -- Defines fields (name, email, message, etc.)
      notification_emails TEXT[], -- Who gets notified?
      created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
  );

  form_submissions (
      id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
      form_id UUID REFERENCES forms(id) ON DELETE CASCADE,
      data JSONB, -- The actual user input
      ip_address INET,
      created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
  );

}



//===========================================
// Module 18: Reputation & Social Proof Module ( 6 Tables)
//===========================================


//===========================================
// Module 19: Lead Capture & Marketing Module ( 3 Tables)
//===========================================


//===========================================
// Module 20:Core Config & Legal Module ( 2 Tables)
//===========================================



















// ========================================================================================================================
// Tourism SaaS Multi-Tenant Master Data Model
// TOTAL: 82 TABLES - FULLY UNABRIDGED
// ==========================================

// --- 01_Core_Tenancy_Identity (17 Tables) ---

agency_prospects [icon: briefcase, color: red] {
  id string [pk]
  agency_status_id string [ref: > agency_statuses.id]
  country_id string [ref: > countries.id]
  agency_name string
  contact_person string
  email string
  phone string
  source string
  notes text
  created_at timestamp
  deleted_at timestamp 
}
agency_prospect_activities [icon: clock, color: gray] {
  id string [pk]
  prospect_id string [ref: > agency_prospects.id]
  admin_user_id string [ref: > users.id]
  action_type string
  feedback text
  created_at timestamp
}






vendor_users [icon: truck, color: purple] {
  id string [pk]
  agency_id string [ref: > agencies.id]
  vendor_type string // 'DRIVER', 'GUIDE', 'HOTEL_STAFF'
  vendor_reference_id string // The Bridge to your profile tables
  email string [unique]
  password_hash string
  full_name string
  is_active boolean [default: true]
  last_login timestamp
  created_at timestamp
  deleted_at timestamp
}
clients [icon: users, color: cyan] {
  id string [pk]
  agency_id string [ref: > agencies.id]
  email string [unique]
  password_hash string
  full_name string
  phone string
  avatar_url string
  is_active boolean [default: true]
  last_login timestamp
  created_at timestamp
  deleted_at timestamp
}
client_profiles [icon: contact, color: cyan] {
  id string [pk]
  client_id string [ref: - clients.id]
  passport_number string
  passport_expiry date
  nationality_id string [ref: > countries.id]
  date_of_birth date
  gender string
  preferred_language_id string [ref: > languages.id]
  preferred_currency_id string [ref: > currencies.id]
  emergency_contact jsonb
  loyalty_points int [default: 0]
}








agency_api_configs [icon: lock, color: red] {
  id uuid pk
  agency_id string [ref: > agencies.id]
  provider_type string // 'whatsapp_meta', 'sendgrid', 'stripe', 'twilio'
  config_key string // e.g., 'API_KEY', 'SECRET_TOKEN', 'WEBHOOK_URL'
  config_value text // Encrypted at rest
  is_encrypted boolean [default: true]
  updated_at timestamp
}
agency_pixels [icon: activity, color: green] {
  id uuid pk
  agency_id string [ref: > agencies.id]
  platform string // 'facebook_pixel', 'google_analytics_v4', 'tiktok_pixel'
  pixel_id string
  script_code text 
  is_active boolean [default: true]
}
agency_landing_pages [icon: layout, color: blue] {
  id uuid pk
  agency_id string [ref: > agencies.id]
  package_id uuid [ref: > tour_packages.id] // Link to specific tour ad
  slug string [unique] // e.g., turkey-summer-deal-2026
  page_title string
  html_content longtext // The agent-provided custom HTML/CSS
  custom_css text
  meta_tags jsonb // For SEO on ads
  status string // 'draft', 'published', 'archived'
  created_at timestamp
  updated_at timestamp
}
landing_page_leads {
  id uuid pk
  landing_page_id uuid [ref: > agency_landing_pages.id]
  lead_id string [ref: > leads.id]
  utm_source string
  utm_medium string
  utm_campaign string
  created_at timestamp
}
agency_domains [icon: globe, color: gray] {
  id uuid pk
  agency_id string [ref: > agencies.id]
  domain_name string [unique] // e.g., tour.agencyname.com
  ssl_status string // 'pending', 'active', 'expired'
  is_primary boolean [default: false]
}



// --- 5. INVENTORY & PRICING (8 Tables) ---



// --- 6. BOOKINGS & FINANCE (7 Tables) ---



accounting_ledger [icon: file-text, color: blue]{
  id string pk
  agency_id string
  booking_id string 
  entry_type string 
  amount decimal
  currency string
  entry_date timestamp
  reference string
  is_adjustment boolean
}

invoices_bookings { 
  id string pk 
  booking_id string 
  invoice_number string 
  status string 
  total decimal 
}

payment_transactions { 
  id string pk 
  booking_id string 
  gateway_id bigint 
  amount decimal 
  status string 
}

tax_settings { 
  id string pk 
  agency_id string 
  name string 
  rate decimal 
}

refunds { 
  id string pk 
  booking_id string 
  amount decimal 
  reason text 
}

// --- 7. CMS & MEDIA (11 Tables) ---



tour_translations [icon: language, color: purple] {
  id string pk
  tour_id string fk
  language_id string fk
  title string
  slug string
  description text
  meta_title string
  meta_description text
}


languages [icon: flag, color: blue] {
  id string pk
  name string
  native_name string
  iso_code string
  is_rtl boolean [default: false]
}

images [icon: image, color: cyan]{
  id string pk
  agency_id string
  imageable_type string
  imageable_id string
  disk string [default: 'r2']
  original_path string
  collection string [default: 'default']
  sort_order int [default: 0]
  variants jsonb
  meta jsonb
  status string 
  is_primary boolean [default: false]
  created_at timestamp
}



legal_documents [icon: file-text, color: red]{
  id string pk
  agency_id string
  documentable_id string
  documentable_type string
  type string
  body_translations jsonb
  version string
  is_active boolean [default: true]
}

inquiries [icon: mail, color: cyan]{
  id string pk
  agency_id string
  inquiry_type string
  payload jsonb
  assigned_to string
  status string
  created_at timestamp
}

// --- 8. SAAS BILLING & SUBSCRIPTION (12 Tables) ---

subscription_plans [icon: price, color: green] {
  id string pk
  name_translations jsonb 
  slug string
  amount decimal
  currency string
  billing_interval string
  trial_period_days int [default: 0]
  is_active boolean [default: true]
}

features [icon: check-circle, color: green] {
  id string pk
  key string
  description string
  value_type string
}

plan_features [icon: link, color: green] {
  id string pk
  plan_id string fk 
  feature_id string fk 
  feature_value string 
}

coupons [icon: tag, color: pink] {
  id string pk
  code string
  discount_type string
  discount_value decimal
  max_redemptions int
  used_count int [default: 0]
  expires_at timestamp
  is_active boolean [default: true]
}

agency_subscriptions [icon: refresh, color: green] {
  id string pk
  agency_id string fk 
  plan_id string fk 
  coupon_id string fk 
  status string
  current_period_start timestamp
  current_period_end timestamp
  cancel_at_period_end boolean [default: false]
  external_subscription_id string 
}

subscription_history [icon: clock, color: gray] {
  id string pk
  agency_id string fk
  old_plan_id string fk
  new_plan_id string fk
  event_type string
  created_at timestamp
}

payment_gateways [icon: credit-card, color: red] {
  id bigint pk
  code int 
  name varchar(40) 
  alias varchar(40) 
  image varchar(255) 
  status tinyint 
  gateway_parameters jsonb 
  supported_currencies jsonb 
  is_crypto boolean 
  created_at timestamp
}

payments [icon: dollar-sign, color: green] {
  id string pk
  agency_id string fk
  subscription_id string fk
  gateway_id bigint fk
  amount decimal
  currency string
  transaction_reference string 
  status string
  created_at timestamp
}

invoices [icon: file-text, color: blue] {
  id string pk
  agency_id string fk
  payment_id string fk
  invoice_number string
  subtotal decimal
  tax_amount decimal
  total decimal
  pdf_url string
  created_at timestamp
}

billing_details { 
  id string pk 
  agency_id string 
  vat_number string 
  billing_address text 
}

gateway_logs { 
  id string pk 
  gateway_id bigint 
  payload jsonb 
  status_code int 
}

subscription_usage { 
  id string pk 
  subscription_id string 
  feature_key string 
  current_usage int 
}

// --- 9. NOTIFICATION & ALERT SYSTEM (8 Tables) ---

notification_channels {
  id string pk
  name string // e.g., 'email', 'sms', 'push', 'whatsapp', 'in_app'
  is_active boolean [default: true]
}

notification_templates {
  id string pk
  agency_id string fk // Nullable for system-wide templates
  event_name string // e.g., 'booking.confirmed', 'lead.assigned'
  channel_id string fk
  subject_translations jsonb
  body_translations jsonb
  is_active boolean [default: true]
  created_at timestamp
}

notifications {
  id uuid pk
  agency_id string fk
  user_id string fk // Recipient
  template_id string fk
  notifier_id string fk // The user or system-process that triggered it
  priority int [default: 1] // 1: Low, 2: Medium, 3: High/Urgent
  data jsonb // Dynamic variables like { "booking_id": "ABC", "customer": "John" }
  is_read boolean [default: false]
  read_at timestamp
  created_at timestamp
}

notification_logs {
  id uuid pk
  notification_id uuid fk
  channel_id string fk
  provider_response jsonb // Stores response from SendGrid, Twilio, etc.
  status string // 'sent', 'failed', 'delivered', 'retry'
  error_message text
  retry_count int [default: 0]
  sent_at timestamp
}

user_notification_preferences {
  id string pk
  user_id string fk
  channel_id string fk
  event_name string
  is_enabled boolean [default: true]
}

agency_notification_settings {
  id string pk
  agency_id string fk
  channel_id string fk
  provider_config jsonb // API keys for SendGrid/Twilio per agency (Multi-tenant)
  is_active boolean [default: true]
}

system_announcements {
  id string pk
  title_translations jsonb
  content_translations jsonb
  target_role string // 'admin', 'agent', 'driver', 'all'
  starts_at timestamp
  ends_at timestamp
  is_active boolean [default: true]
}

announcement_reads {
  id string pk
  announcement_id string fk
  user_id string fk
  read_at timestamp
}

// --- 10. MESSAGING, LIVE SUPPORT & WHATSAPP (10 Tables) ---

conversation_threads {
  id uuid pk
  agency_id string fk
  customer_id string // Nullable if B2B (User to User)
  assigned_agent_id string fk
  source string // 'whatsapp', 'website_chat', 'internal', 'email'
  status string // 'open', 'pending', 'resolved', 'closed'
  last_message_at timestamp
  created_at timestamp
}

messages {
  id uuid pk
  thread_id uuid fk
  sender_id string fk // Can be User ID or Customer Reference
  sender_type string // 'agent', 'customer', 'system'
  message_type string // 'text', 'image', 'file', 'template'
  body text
  attachment_url string
  whatsapp_message_sid string // Reference ID from WhatsApp API
  is_read boolean [default: false]
  created_at timestamp
}

whatsapp_agent_configs {
  id string pk
  agency_id string fk
  agent_id string fk
  phone_number_id string // WhatsApp Business ID
  whatsapp_business_account_id string
  access_token text // Encrypted API Token
  webhook_verify_token string
  is_active boolean [default: true]
  api_provider string [default: 'meta'] // 'meta', 'twilio', 'gupshup'
}

whatsapp_templates {
  id string pk
  agency_id string fk
  template_name string
  language_code string
  category string // 'marketing', 'utility', 'authentication'
  components jsonb // Header, Body, Buttons
  approval_status string
}

chat_widgets {
  id string pk
  agency_id string fk
  website_domain string
  widget_config jsonb // Colors, Welcome message, Position
  is_active boolean [default: true]
}

support_tickets {
  id string pk
  agency_id string fk
  conversation_id uuid fk
  priority string // 'low', 'medium', 'high', 'emergency'
  subject string
  category string // 'billing', 'technical', 'booking_change'
  resolution_notes text
}

agent_availability {
  id string pk
  user_id string fk
  is_online boolean [default: false]
  current_load int [default: 0] // Count of active chats
  last_seen_at timestamp
}

canned_responses {
  id string pk
  agency_id string fk
  shortcut string // e.g., "/welcome"
  message_translations jsonb
}

message_reactions {
  id uuid pk
  message_id uuid fk
  user_id string fk
  reaction string // emoji
}

whatsapp_logs {
  id uuid pk
  agency_id string fk
  agent_id string fk
  direction string // 'inbound', 'outbound'
  status string // 'delivered', 'read', 'failed'
  error_details jsonb
  created_at timestamp
}

// --- 11. BLOG & CONTENT MARKETING (8 Tables) ---







// --- 13.  PRICING & PROFIT ENGINE (6 tables) ---







// --- 14. AMENITIES & SERVICES MASTER SYSTEM  (4 Tables)---

// MASTER AMENITIES (Reusable Inclusions/Exclusions)













// --- 16. BOOKING & DOCUMENTS (4 Tables) ---

bookings [icon: check-circle, color: green] {
  id uuid pk
  agency_id string [ref: > agencies.id]
  lead_id string [ref: > leads.id]
  offer_label_id uuid [ref: > offer_labels.id] // The specific tier they bought
  reservation_code string [unique] // e.g., "TRV-2026-X89"
  status string // 'confirmed', 'in_progress', 'completed', 'cancelled'
  
  // Primary Passenger Info
  main_contact_name string
  main_contact_phone string
  
  payment_status string // 'unpaid', 'partially_paid', 'fully_paid'
  created_by string [ref: > users.id]
  total_booking_price decimal // Captured from offer_label.total_price
  currency_id string
  
  notes text
  created_at timestamp
}

booking_passengers [icon: users, color: green] {
  id uuid pk
  booking_id uuid [ref: > bookings.id]
  full_name string
  passport_number string
  passport_expiry_date date
  passport_copy_path string // File path
  air_ticket_path string    // File path
  date_of_birth date
  nationality_id string [ref: > countries.id]
}

// Sub-reservations for every item in the itinerary (Hotels, Guides, etc.)
booking_supplier_confirmations [icon: clock, color: orange] {
  id uuid pk
  booking_id uuid [ref: > bookings.id]
  offer_item_id uuid [ref: > offer_items.id] // Link to the specific hotel/guide
  
  supplier_status string // 'pending_request', 'confirmed', 'waitlist', 'rejected'
  supplier_ref_number string // The Hotel's own confirmation number
  
  final_net_cost decimal // Final price agreed with supplier
  email_sent_to_supplier boolean [default: false]
  cancellation_sent boolean [default: false]
  
  notes text
  updated_at timestamp
}

booking_vouchers [icon: file-text, color: gray] {
  id uuid pk
  booking_id uuid [ref: > bookings.id]
  voucher_type string // 'hotel_voucher', 'transfer_voucher'
  file_path string
  generated_at timestamp
}

// --- 17. ACCOUNTING & LEDGER (6 Tables) ---

// 1. ACCOUNTS RECEIVABLE (Money from Customer)
client_payments [icon: arrow-down-circle, color: blue] {
  id uuid pk
  agency_id string [ref: > agencies.id]
  booking_id uuid [ref: > bookings.id]
  amount_received decimal
  currency_id string
  exchange_rate_to_base decimal [default: 1.0]
  payment_method string // 'wire', 'stripe', 'cash'
  transaction_ref string
  payment_date date
  status string // 'pending', 'cleared', 'failed'
}

// 2. ACCOUNTS PAYABLE (Money to Suppliers)
supplier_payments [icon: arrow-up-circle, color: red] {
  id uuid pk
  agency_id string [ref: > agencies.id]
  booking_id uuid [ref: > bookings.id]
  supplier_confirmation_id uuid [ref: > booking_supplier_confirmations.id]
  
  payable_type string // 'hotel', 'driver', 'guide', 'operator'
  payable_id uuid // ID of the specific driver/hotel
  
  amount_due decimal
  amount_paid decimal [default: 0]
  currency_id string
  due_date date
  paid_date date
  payment_status string // 'unpaid', 'partially_paid', 'paid'
}

// 3. SALES COMMISSIONS TRACKING
sales_payouts [icon: award, color: yellow] {
  id uuid pk
  agency_id string [ref: > agencies.id]
  booking_id uuid [ref: > bookings.id]
  sales_user_id string [ref: > users.id]
  commission_amount decimal
  is_paid boolean [default: false]
  payout_date date
}

// 4. THE MASTER LEDGER (Profit & Loss)
agency_ledger [icon: columns, color: purple] {
  id uuid pk
  agency_id string [ref: > agencies.id]
  booking_id uuid [ref: > bookings.id]
  
  total_revenue decimal // Money from client
  total_cost_suppliers decimal // Sum of all supplier payments
  total_commission_cost decimal // Sales cost
  
  net_profit decimal // (Revenue - Cost - Commission)
  margin_percentage decimal
  
  fiscal_period string // e.g., "2026-Q1"
  created_at timestamp
}

// 5. TAX & INVOICING
accounting_invoices [icon: file-plus, color: gray] {
  id uuid pk
  booking_id uuid [ref: > bookings.id]
  invoice_number string [unique]
  tax_amount decimal
  grand_total decimal
  pdf_url string
  issued_at timestamp
}

// 6. DEBTOR/CREDITOR TRACKING
balance_sheets {
  id uuid pk
  agency_id string [ref: > agencies.id]
  entity_type string // 'customer' or 'supplier'
  entity_id string // ID of user or hotel
  current_balance decimal
  last_transaction_at timestamp
}



// --- 19. REVIEW & REPUTATION SYSTEM (4 Tables) ---

package_reviews [icon: star, color: yellow] {
  id uuid pk
  agency_id string [ref: > agencies.id]
  package_id uuid [ref: > tour_packages.id]
  booking_id uuid [ref: > bookings.id] // Verified purchase link
  user_id string [ref: > users.id]    // The traveler who wrote it
  
  rating_score int [note: '1 to 5 stars']
  comment_title string
  comment_body text
  
  // Specific Rating Metrics (Stored as JSON for performance)
  // { "guides": 5, "transport": 4, "value": 5, "accommodation": 3 }
  rating_metrics jsonb 
  
  status string // 'pending', 'published', 'hidden', 'flagged'
  is_verified_booking boolean [default: true]
  likes_count int [default: 0]
  
  created_at timestamp
  updated_at timestamp
}

review_replies [icon: message-square, color: blue] {
  id uuid pk
  review_id uuid [ref: > package_reviews.id]
  agency_user_id string [ref: > users.id] // The staff member responding
  reply_body text
  created_at timestamp
}

review_media [icon: image, color: cyan] {
  id uuid pk
  review_id uuid [ref: > package_reviews.id]
  media_type string // 'image', 'video'
  file_path string
  sort_order int
}

package_rating_stats [icon: bar-chart, color: orange] {
  id uuid pk
  package_id uuid [ref: > tour_packages.id]
  average_rating decimal [default: 0]
  total_reviews_count int [default: 0]
  rating_distribution jsonb // { "5": 120, "4": 45, "3": 10... }
  last_updated_at timestamp
}




