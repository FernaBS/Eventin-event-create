<?php
/**
 * Plugin Name: event-create-event
 * Plugin URI:  http://tusitio.com/mi-plugin
 * Description: Un plugin de ejemplo que muestra un mensaje con un shortcode.
 * Version:     1.0
 * Author:      Tu Nombre
 * Author URI:  http://tusitio.com
 * License:     GPL2
 */

// Evitar el acceso directo al archivo
if ( !defined('ABSPATH') ) {
    die;
}

/*function filter_post_data( $data, $postarr, $update ) {
    if($data['post_type'] === 'etn'){
        if(!$update){
            $params = [
                'post_title' => $data['post_title'],
                'ID' => $postarr['ID'],
                'nombre' => $data['post_title'],
                'fecha_de_inicio' => $data['post_date'],
                'fecha_de_culminacion' => $data['post_date']
            ];
            pods('area')->add($params);
        }
        else{
            file_put_contents('varDump.txt', $data);
        }
    }
    return $data;
} 
add_filter('wp_insert_post_data', 'filter_post_data', 10, 3);*/


function filter_post_data( $event, $request ) {
    $event_data = prepare_item_for_database($request);
    $params = [
        'post_title' => $event_data['post_title'],
        'nombre' => $event_data['post_title'],
        'fecha_de_inicio' => $event_data['etn_start_date'],
        'fecha_de_culminacion' => $event_data['etn_end_date'],
        'eventin_id' => $event->id
    ];
    pods('area')->add($params);
}
add_action('eventin_event_created', 'filter_post_data', 10, 2);

function update( $event, $request ) {
    $event_data = prepare_item_for_database($request);
    // $params = [
    //     'post_title' => $event_data['post_title'],
    //     'nombre' => $event_data['post_title'],
    //     'fecha_de_inicio' => $event_data['etn_start_date'],
    //     'fecha_de_culminacion' => $event_data['etn_end_date']
    // ];
    $pod = pods('area', ['where' => "t.id = '$event->id' and t.post_status = 'Draft'"]);
    $pod -> add_to('nombre', $event_data['post_title']);
}
add_action('eventin_event_updated', 'update', 10, 2);

function prepare_item_for_database( $request ) {
    $input_data = json_decode( $request->get_body(), true ) ?? [];
     $validate   = etn_validate( $input_data, [
        'title'      => [
            'required',
        ],
        'timezone'   => [
            'required',
        ],
        'start_date' => [
            'required',
        ],
        'end_date'   => [
            'required',
        ],
        'start_time' => [
            'required',
        ],
        'end_time'   => [
            'required',
        ],
    ] );

    if ( is_wp_error( $validate ) ) {
        return $validate;
    }

    $event_data = [];
    if ( isset( $input_data['title'] ) ) {
        $event_data['post_title'] = $input_data['title'];            
    }        
    

    if ( isset( $input_data['description'] ) ) {
        $event_data['post_content'] = $input_data['description'];
    }

    if ( isset( $input_data['schedule_type'] ) ) {
        $event_data['etn_select_speaker_schedule_type'] = $input_data['schedule_type'];
    }

    // if ( isset( $input_data['organizer'] ) ) {
    //     $event_data['etn_event_organizer'] = $this->prepare_organizer( $input_data );
    // }

    // if ( isset( $input_data['speaker'] ) ) {
    //     $event_data['etn_event_speaker'] = $this->prepare_speaker( $input_data );
    // }

    if ( isset( $input_data['timezone'] ) ) {
        $event_data['event_timezone'] = $input_data['timezone'];
    }

    if ( isset( $input_data['start_date'] ) ) {
        $event_data['etn_start_date'] = $input_data['start_date'];
    }

    if ( isset( $input_data['end_date'] ) ) {
        $event_data['etn_end_date'] = $input_data['end_date'];
    }

    if ( isset( $input_data['start_time'] ) ) {
        $event_data['etn_start_time'] = $input_data['start_time'];
    }

    if ( isset( $input_data['end_time'] ) ) {
        $event_data['etn_end_time'] = $input_data['end_time'];
    }

    if ( isset( $input_data['ticket_availability'] ) ) {
        $event_data['etn_ticket_availability'] = $input_data['ticket_availability'];
    }

    if ( isset( $input_data['ticket_variations'] ) ) {
        $event_data['etn_ticket_variations'] = $input_data['ticket_variations'];
    }

    if ( isset( $input_data['event_logo'] ) ) {
        $event_data['etn_event_logo'] = $input_data['event_logo'];
    }

    if ( isset( $input_data['event_logo_id'] ) ) {
        $event_data['event_logo_id'] = $input_data['event_logo_id'];
    }

    if ( isset( $input_data['event_banner_id'] ) ) {
        $event_data['event_banner_id'] = $input_data['event_banner_id']; 
    }

    if ( isset( $input_data['calendar_text_color'] ) ) {
        $event_data['etn_event_calendar_text_color'] = $input_data['calendar_text_color'];
    }

    if ( isset( $input_data['registration_deadline'] ) ) {
        $event_data['etn_registration_deadline'] = $input_data['registration_deadline'];
    }

    if ( isset( $input_data['attende_page_link'] ) ) {
        $event_data['attende_page_link'] = $input_data['attende_page_link'];
    }

    if ( isset( $input_data['zoom_id'] ) ) {
        $event_data['etn_zoom_id'] = $input_data['zoom_id'];
    }

    if ( isset( $input_data['location_type'] ) ) {
        $event_data['etn_event_location_type'] = $input_data['location_type'];
    }

    if ( isset( $input_data['location'] ) ) {
        $event_data['etn_event_location'] = $input_data['location'];
    }

    if ( isset( $input_data['zoom_event'] ) ) {
        $event_data['etn_zoom_event'] = $input_data['zoom_event'];
    }

    if ( isset( $input_data['total_ticket'] ) ) {
        $event_data['etn_total_avaiilable_tickets'] = $input_data['total_ticket'];
    }

    if ( isset( $input_data['google_meet'] ) ) {
        $event_data['etn_google_meet'] = $input_data['google_meet'];
    }

    if ( isset( $input_data['google_meet_description'] ) ) {
        $event_data['etn_google_meet_short_description'] = $input_data['google_meet_description'];
    }

    if ( isset( $input_data['fluent_crm'] ) ) {
        $event_data['fluent_crm'] = $input_data['fluent_crm'];
    }

    if ( isset( $input_data['location_type'] ) ) {
        $event_data['etn_event_location_type'] = $input_data['location_type'];
    }

    if ( isset( $input_data['event_socials'] ) ) {
        $event_data['etn_event_socials'] = $input_data['event_socials'];
    }

    if ( isset( $input_data['schedules'] ) ) {
        $event_data['etn_event_schedule'] = $input_data['schedules'];
    }

    if ( isset( $input_data['categories'] ) ) {
        $event_data['categories'] = $input_data['categories'];
    }

    if ( isset( $input_data['tags'] ) ) {
        $event_data['tags'] = $input_data['tags'];
    }

    if ( isset( $input_data['faq'] ) ) {
        $event_data['etn_event_faq'] = $input_data['faq'];
    }

    if ( isset( $input_data['extra_fields'] ) ) {
        $event_data['attendee_extra_fields'] = $input_data['extra_fields'];
    }

    // Support speaker and organizer group.
    if ( isset( $input_data['speaker_type'] ) ) {
        $event_data['speaker_type'] = $input_data['speaker_type'];
    }

    if ( isset( $input_data['speaker_group'] ) ) {
        $event_data['speaker_group'] = $input_data['speaker_group'];
    }

    if ( isset( $input_data['organizer_type'] ) ) {
        $event_data['organizer_type'] = $input_data['organizer_type'];
    }

    if ( isset( $input_data['organizer_group'] ) ) {
        $event_data['organizer_group'] = $input_data['organizer_group'];
    }

    if ( isset( $input_data['fluent_crm_webhook'] ) ) {
        $event_data['fluent_crm_webhook'] = $input_data['fluent_crm_webhook'];
    }

    // Recurring event data.
    if ( isset( $input_data['recurring_enabled'] ) ) {
        $event_data['recurring_enabled'] = $input_data['recurring_enabled'];
    }

    if ( isset( $input_data['event_recurrence'] ) ) {
        $event_data['etn_event_recurrence'] = $input_data['event_recurrence'];
    }

    // RSVP support.
    if ( isset( $input_data['rsvp_settings'] ) ) {
        $event_data['rsvp_settings'] = $input_data['rsvp_settings'];
    }

    // Seat Plan Support.
    if ( isset( $input_data['seat_plan'] ) ) {
        $event_data['seat_plan'] = $input_data['seat_plan'];
    }

    if ( isset( $input_data['seat_plan_settings'] ) ) {
        $event_data['seat_plan_settings'] = $input_data['seat_plan_settings'];
    }

    // Template support.
    if ( isset( $input_data['ticket_template'] ) ) {
        $event_data['ticket_template'] = $input_data['ticket_template'];
    }

    if ( isset( $input_data['certificate_template'] ) ) {
        $event_data['certificate_template'] = $input_data['certificate_template'];
    }

    if ( isset( $input_data['external_link'] ) ) {
        $event_data['external_link'] = $input_data['external_link'];
    }

    if ( isset( $input_data['event_banner'] ) ) {
        $event_data['event_banner'] = $input_data['event_banner'];
    }

    if ( isset( $input_data['event_layout'] ) ) {
        $event_data['event_layout'] = $input_data['event_layout'];
    }

    if ( isset( $input_data['status'] ) ) {
        $event_data['post_status'] = $input_data['status'];
    }

    if ( ! empty( $input_data['event_slug'] ) ) {
        $event_data['post_name'] = sanitize_title( $input_data['event_slug'] );
    }

    if ( isset( $input_data['event_type'] ) ) {
        $event_data['event_type'] = $input_data['event_type'];
    }

    if ( isset( $input_data['location'] ) ) {
        $event_data['location'] = $input_data['location'];
    }

    // certificate prefference.
    if ( isset( $input_data['certificate_preference'] ) ) {
        $event_data['certificate_preference'] = $input_data['certificate_preference'];
    }

    if ( isset( $input_data['virtual_product'] ) ) {
        $event_data['virtual'] = $input_data['virtual_product'];
    }

    
    
    return $event_data;
}

