<?php

add_action('admin_menu', 'custom_menu');

function custom_menu()
{
    add_menu_page(
        'Reports',
        'Reports',
        'edit_posts',
        'class_reporting',
        'wc_events_reporting_base_handler',
        'dashicons-admin-generic'
    );
}

function wc_events_reporting_base_handler()
{
    switch (@$_GET['qtype']) {
        case 'dailyall':
            wc_events_reporting_daily_all_view();
            break;
        case 'daily':
            wc_events_reporting_daily_view();
            break;
        case 'all':
            wc_events_reporting_all_view();
            break;
        case 'all_detailed':
            wc_events_reporting_all_detailed_view();
            break;
        case 'download':
            wc_events_download();
            break;
        default:
            wc_events_reporting_base_view();
    }
}

function get_order_against_product($prod, $order_status, $date = false)
{
    global $wpdb;
    $date_string = "";
    if ($date && is_array($date)) {
        $date_string = "AND posts.post_date >= '" . $date['start'] . "' AND posts.post_date <= '" . $date['end'] . "'";
    }
    if (is_array($prod)) {
        $prod = implode(",", $prod);
    }

    $ticket_args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'meta_query' => array(
            array(
                'key' => '_tribe_wooticket_for_event',
                'value' => $prod,
                'compare' => 'IN'
            )
        )
    );
    $ticket_query = new WP_Query($ticket_args);

    $product_ids1 = implode(',', wp_list_pluck($ticket_query->posts, 'ID'));

    if (!$product_ids1) {
        return;
    }

    $query = "SELECT posts.ID as order_id,order_items.order_item_id FROM 24GgrmQ_posts AS posts INNER JOIN 24GgrmQ_woocommerce_order_items AS order_items ON posts.ID = order_items.order_id INNER JOIN 24GgrmQ_woocommerce_order_itemmeta AS order_item_meta__qty ON (order_items.order_item_id = order_item_meta__qty.order_item_id) AND (order_item_meta__qty.meta_key = '_qty') INNER JOIN 24GgrmQ_woocommerce_order_itemmeta AS order_item_meta__product_id_array ON order_items.order_item_id = order_item_meta__product_id_array.order_item_id WHERE posts.post_type IN ( 'shop_order' ) " . $date_string . " AND posts.post_status IN (" . $order_status . ") AND ( ( order_item_meta__product_id_array.meta_key IN ('_product_id','_variation_id') AND order_item_meta__product_id_array.meta_value IN ($product_ids1) ))";
    //echo($query);
    $query_data = $wpdb->get_results($query);

    if (empty($query_data)) {
        return false;
    }
    $data = array();
    $i = 0;
    $additionalInfo = array();
    $additionalColumn = array();
    foreach ($query_data as $order_Data) {
        $wc_ord_obj = wc_get_order($order_Data->order_id);
        $val_Extra = '';
        $get_item_data = wc_get_order_item_meta($order_Data->order_item_id, null);
        $method = get_post_meta($order_Data->order_id, '_payment_method_title', TRUE);
        if (!$method || $method == "") {
            $method = get_post_meta($order_Data->order_id, '_payment_method', TRUE);
            if ($method == "cheque") {
                $method = "Check";
            }
            if ($method == "cod") {
                $method = "Cash";
            }
        }
        @$ref = explode("_", $get_item_data['_refdata'][0]);
        if (!isset($ref[1])) {
            @$ref = explode("-", $get_item_data['_refdata'][0]);
        }
        @$get_extra_options = maybe_unserialize($get_item_data['_tmcartepo_data'][0]);
        $val_Extra = "";
        $additionalExtrasVal = array();
        if (is_array($get_extra_options)) {
            foreach ($get_extra_options as $extra_inputs) {

                if (is_object($extra_inputs)) {
                    $extra_inputs = objectToArray($extra_inputs);
                }
                $ekey = $extra_inputs['name'];
                $additionalColumn[] = $ekey;
                $str = $extra_inputs['value'];
                $val_Extra .= $str;
                $val_Extra .= ",";
                $additionalExtrasVal[$ekey] = $extra_inputs['value'];
            }
            $val_Extra = trim($val_Extra, ",");
        }

        $data[$i]['type'] = 'adult';
        // if ($getL['form_id'] == "1") {
        //     $data[$i]['fname'] = $getL['4.3'];
        //     $data[$i]['lname'] = $getL['4.6'];
        //     $data[$i]['contact Number'] = $getL[11];
        // } else {
        //     $data[$i]['fname'] = $getL['1.3'];
        //     $data[$i]['lname'] = $getL['1.6'];
        //     $data[$i]['contact Number'] = $getL[7];
        // }

        $ticket_args = array(
            'post_type' => 'tribe_wooticket',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'orderby' => 'title',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => '_tribe_wooticket_order',
                    'value' => $order_Data->order_id,
                    'compare' => '=',
                )
            )
        );
        $ticket_query = new WP_Query($ticket_args);
        $attendee_meta = get_post_meta($ticket_query->posts[0]->ID);

        $attendee_data = maybe_unserialize($attendee_meta['_tribe_tickets_meta'][0]);

        @$data[$i]['fname'] = $attendee_data['name-of-participant'] ?: $attendee_data['participant-name'];
        @$data[$i]['lname'] = '';
        @$data[$i]['gender'] = $attendee_data['gender'];
        @$data[$i]['Parent Name'] = $attendee_data['emergency-contact-namecalled-if-parents-are-not-available'];

        @$data[$i]['Payment mode'] = $method;
        @$data[$i]['user_email'] = $wc_ord_obj->billing_email;
        @$data[$i]['user_id'] = $ref[1];
        @$data[$i]['_refdata'] = $get_item_data['_refdata'][0];
        @$data[$i]['price'] = $get_item_data['_line_total'][0];
        @$data[$i]['order-date'] = $wc_ord_obj->order_date;
        @$data[$i]['age'] = "";
        @$data[$i]['grade'] = "";

        @$data[$i]['Emergency Contact Name'] = $attendee_data['emergency-contact-namecalled-if-parents-are-not-available'];
        @$data[$i]['Emergency contact phone'] = "";
        @$data[$i]['town_ship_readington'] = $get_item_data["I give the Township of Readington Permission to print my child or my Picture"][0];
        @$data[$i]['Please list medical concerns,food allergies or other know allergies instructors need to know'] = $attendee_data['please-list-medical-concernsfood-allergies-or-other-known-allergies-instructors-need-to-know'];
        @$data[$i]["_tmcartepo_data"] = $get_extra_options;


        if (isset($get_item_data["Emergency Contact Name"][0])) {

            $data[$i]['dob'] = $get_item_data["Date of birth"][0];
            if ($data[$i]['dob'] == '') {
                // $data[$i]['dob'] = $getL[3];
            }
            if (DateTime::createFromFormat('m/d/Y', $data[$i]['dob']) !== FALSE || DateTime::createFromFormat('Y-m-d', $data[$i]['dob']) !== FALSE) {
                // $data[$i]['age'] = getAge($data[$i]['dob']);
            }
            // $data[$i]['gender'] = ($getL[4]);
            $data[$i]['grade'] = $get_item_data["Grade"][0];

            $data[$i]['type'] = 'child';
            $data[$i]['Emergency Contact Name'] = $get_item_data["Emergency Contact Name"][0];
            $data[$i]['Emergency contact phone'] = $get_item_data["Emergency Contact Phone"][0];
            $data[$i]['Please list medical concerns,food allergies or other know allergies instructors need to know'] = $attendee_data["please-list-medical-concernsfood-allergies-or-other-known-allergies-instructors-need-to-know"];
            $data_user = GFAPI::get_entry(get_user_meta($wc_ord_obj->customer_user, 'entry_id', TRUE));
            $data[$i]['Home address'] = (!is_wp_error($data_user)) ? ($data_user["3.1"] . " " . $data_user["3.2"] . " " . $data_user["3.3"]) : "";
            $data[$i]['Home phone'] = (!is_wp_error($data_user)) ? ($data_user[5]) : "";
            if ($data[$i]['contact Number'] == '') {
                $data[$i]['contact Number'] = (!is_wp_error($data_user)) ? $data_user[11] : "";
            }
            $data[$i]['Parent Name'] = (!is_wp_error($data_user)) ? $data_user['4.3'] . ' ' . $data_user['4.6'] : "";
        }

        $data[$i]['order-id'] = $order_Data->order_id;
        $data[$i]['product-id'] = $get_item_data['_product_id'][0];
        $data[$i]['Extra options'] = $val_Extra;
        $data[$i]['AdditionalOptions'] = $additionalExtrasVal;
        $additionalInfo[$i] = $val_Extra;

        $i++;
    }

    $data = array_reverse($data);
    $additionalInfo = array_reverse($additionalInfo);
    $additionalColumn = array_unique($additionalColumn);
    $data["ExtraColumn"] = $additionalColumn;

    return $data;
}

// This function generates and outputs the report body rows
function hm_sbp_export_body2($dest, $return = false, $cats_f, $date = array())
{
    ob_start();
    global $woocommerce, $wpdb;
    $product_ids = array();
    $cats = array();
    foreach (array($cats_f) as $cat)
        if (is_numeric($cat))
            $cats[] = $cat;
    $product_ids = get_objects_in_term($cats, 'tribe_events_cat');

    // Calculate report start and end dates (timestamps)
    if (is_array($date) && isset($date['start'])) {
        $end_date = strtotime('midnight', strtotime($date['end']));
        $start_date = strtotime('midnight', strtotime($date['start']));
    } else {
        $end_date = strtotime('midnight', current_time('timestamp')) - 86400;
        $start_date = $end_date - (86400 * 30);
    }
    // Assemble order by string
    $orderby = (in_array(@$_POST['orderby'], array('product_id', 'gross', 'gross_after_discount')) ? $_POST['orderby'] : 'quantity');
    $orderby .= ' ' . (@$_POST['orderdir'] == 'asc' ? 'ASC' : 'DESC');
    // Create a new WC_Admin_Report object
    include_once($woocommerce->plugin_path() . '/includes/admin/reports/class-wc-admin-report.php');
    $wc_report = new WC_Admin_Report();
    $wc_report->start_date = $start_date;
    $wc_report->end_date = $end_date;
    $date1 = new DateTime();
    $date1->setTimestamp($start_date);
    $newStartDate = $date1->format("Y-m-d");
    $date1->setTimestamp($end_date);
    $newEndDate = $date1->format("Y-m-d") . ' 23:59:59';

    // Order status filter
    $wcOrderStatuses = wc_get_order_statuses();
    $orderStatuses = array();
    //"'wc-completed','wc-processing','wc-on-hold'"
    foreach (array('wc-processing') as $orderStatus) {
        if (isset($wcOrderStatuses[$orderStatus]))
            $orderStatuses[] = substr($orderStatus, 3);
    }
    $result = array();
    $event_ids = implode(",", $product_ids);

    $ticket_args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'meta_query' => array(
            array(
                'key' => '_tribe_wooticket_for_event',
                'value' => $event_ids,
                'compare' => 'IN',
            )
        )
    );
    $ticket_query = new WP_Query($ticket_args);

    $product_ids1 = implode(',', wp_list_pluck($ticket_query->posts, 'ID'));

    $sql = "SELECT  posts.ID ,order_items.order_item_name,order_item_meta__line_total.meta_value,order_item_meta__product_id_array.meta_value AS product_id   FROM 24GgrmQ_posts AS posts 
    INNER JOIN 24GgrmQ_woocommerce_order_items AS order_items ON posts.ID = order_items.order_id INNER JOIN 24GgrmQ_woocommerce_order_itemmeta AS order_item_meta__line_total ON (order_items.order_item_id = order_item_meta__line_total.order_item_id)  AND (order_item_meta__line_total.meta_key = '_line_total') INNER JOIN 24GgrmQ_woocommerce_order_itemmeta AS order_item_meta__product_id_array ON order_items.order_item_id = order_item_meta__product_id_array.order_item_id WHERE   posts.post_type     IN ( 'shop_order','shop_order_refund' ) AND     posts.post_status   IN ( 'wc-completed','wc-processing','wc-on-hold') AND   posts.post_date >= '{$newStartDate}' AND    posts.post_date <= '{$newEndDate}' AND ( ( order_item_meta__product_id_array.meta_key   IN ('_product_id','_variation_id') AND order_item_meta__product_id_array.meta_value IN ({$product_ids1}) ))";

    $result = $wpdb->get_results($sql);

    if (count($result) > 0) {
        $ReportArr = array();
        //$ReportArr
        $nps_gateway = 0;
        $cheque = 0;
        foreach ($result as $key => $val) {
            extract((array) $val);

            $paymentMeyhod = get_post_meta($ID, "_payment_method", true);

            if ($paymentMeyhod == "nps_gateway" || $paymentMeyhod == "stripe") {
                $ReportArr[$product_id]["nps_gateway"][] = array("product_title" => $order_item_name, "product_id" => $product_id, "_payment_method" => $paymentMeyhod, "soldPrice" => $meta_value);
            }
            if ($paymentMeyhod == "cheque" || $paymentMeyhod == "cod") {
                $ReportArr[$product_id]["cheque"][] = array("product_title" => $order_item_name, "product_id" => $product_id, "_payment_method" => $paymentMeyhod, "soldPrice" => $meta_value);
            }
        }
    }
    if ($return)
        $rows = array();

    if ($return) {
        return $ReportArr;
    }
}

function make_csv_file_to_download($csv_array)
{
    $name = get_template_directory() . '/result.csv';
    $file = fopen($name, "w+");
    fputcsv($file, array_keys($csv_array[0]));
    foreach ($csv_array as $data) {
        $line = '';
        foreach ($data as $vals) {
            $line .= $vals;
            $line .= '--';
        }
        fputcsv($file, explode('--', $line));
    }
    fclose($file);
    return get_template_directory() . '/download.php?f=result.csv';
}

add_action('wp_ajax_reprt_pdf_exp', 'wc_events_report_pdf_exp');

function wc_events_report_pdf_exp()
{
    require_once 'includes/dompdf/dompdf_config.inc.php';

    $dompdf = new DOMPDF();
    $styles = '<style type="text/css">         table tr td {    width: auto;    text-align: center;    padding:10px 0;}table thead,table tfoot {    font-weight: bold;    background: #17a7e0;    color: #fff;}.table-part-all tr td{    width: 12.12%}.table-part-all tr td.ph_no {    width: 15%;}.table-part-all tr td.age {    width: 5%;}.table-part-all tr td.grade {    width: 7%;}.table-part-all tr td.ka {    width: 20%;.daily_reprt {    display: inline-block;    width: 100%;}.export_csv {    float: right;    margin-right: 10px !important;}h3 i,.wrap.woocommerce h5 i {    color: #f5841f;}table {    margin-bottom: 60px;    width: 100%;    /*margin-left: 25px;*/}tr td{    width: 12.12%;    color: black;    font-size: 10pt;}tbody tr td {text-align: left;}</style>';
    $PDFhtml = $styles . $_POST['html_wrap'];
    $dompdf->set_paper("A4", "landscape");
    $dompdf->load_html($PDFhtml);
    $dompdf->render();
    echo $dompdf->output();
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="some.pdf"');
    header('Content-Transfer-Encoding: binary');
    header('Connection: Keep-Alive');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    exit();
}
