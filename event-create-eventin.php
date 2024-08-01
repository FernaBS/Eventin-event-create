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

/**
 * Create event and introduce it in the exposicion pod
 * 
 * @param array $event Event data
 * @param array $request Full details about the request.
 */

function filter_post_data( $event, $request ) {
    $event_data = prepare_item_for_database($request);
    $params = [
        'post_title' => $event_data['post_title'],
        'fecha_de_inicio' => $event_data['etn_start_date'],
        'fecha_de_culminacion' => $event_data['etn_end_date'],
        'id' => $event->id,
        'zona_de_la_nave' => $event_data['etn_event_location']['address']
    ];

    pods('exposicion')->add($params);

}
add_action('eventin_event_created', 'filter_post_data', 10, 2);

/**
 * Update event of exposicion pod
 * 
 * @param array $event Event data
 * @param array $request Full details about the request.
 */
function update_post_data($event, $request) {
    $event_data = prepare_item_for_database($request);
    $pod = pods('exposicion', $event->id);

    $fields_to_save = [
        'post_title' => $event_data['post_title'],
        'fecha_de_inicio' => $event_data['etn_start_date'],
        'fecha_de_culminacion' => $event_data['etn_end_date'],
        'zona_de_la_nave' => $event_data['etn_event_location']['address'],
        'featured_image' => $event_data['event_banner'],
    ];

    $organizer_names = [];
    if( isset($event_data['etn_event_organizer']) ){
        foreach ($event_data['etn_event_organizer'] as $organizer_id) {
            $organizer_name = get_the_title($organizer_id);
            $organizer_names[] = $organizer_name;
        }
    }
    $fields_to_save['artistas'] = $organizer_names;

    foreach ($fields_to_save as $field_name => $field_value) {
        $pod->save($field_name, $field_value, $event->id);
    }
}
add_action('eventin_event_updated', 'update_post_data', 10, 2);

/**
 * Prepare the item for create or update operation.
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_Error|object $prepared_item
 */
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
    
    if ( isset( $input_data['organizer'] ) ) {
        $event_data['etn_event_organizer'] = prepare_organizer( $input_data );
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

    if ( isset( $input_data['location'] ) ) {
        $event_data['etn_event_location'] = $input_data['location'];
    }

    if ( isset( $input_data['categories'] ) ) {
        $event_data['categories'] = $input_data['categories'];
    }

    if ( isset( $input_data['event_banner'] ) ) {
        $event_data['event_banner'] = $input_data['event_banner'];
    }

    return $event_data;
}

/**
 * Prepare organizer for the event
 *
 * @param   array  $args  [$args description]
 *
 * @return  array
 */
function prepare_organizer( $args = [] ) {
    $orgnizer_type    = isset( $args['organizer_type'] ) ? $args['organizer_type'] : '';
    $organizers       = isset( $args['organizer'] ) ? $args['organizer'] : '';

    if ( 'single' === $orgnizer_type ) {
        return $organizers;
    }

    return null;
}

