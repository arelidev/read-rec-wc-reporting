<?php

function ExportXLS($data, $type = '') {
	
    require_once 'includes/PHPExcel.php';

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getProperties()->setCreator("Runnable.com");
    $objPHPExcel->getProperties()->setLastModifiedBy("Runnable.com");
    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
    $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX,  generated using PHP classes.");
    $objPHPExcel->setActiveSheetIndex(0);
    if ($type == 'class') {
        if ($data && is_array($data)) {
            $get_headers = array_keys($data[0]);
            $makeABSarray = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            foreach ($get_headers as $k => $headers) {
                $objPHPExcel->getActiveSheet()->SetCellValue($makeABSarray[$k] . '1', $headers);
            }
            $incr = 2;
            foreach ($data as $data_to_exp) {
                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $incr, $data_to_exp['type']);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $incr, $data_to_exp['fname']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $incr, $data_to_exp['lname']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $incr, $data_to_exp['contact Number']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E' . $incr, $data_to_exp['Payment mode']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F' . $incr, $data_to_exp['user_email']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G' . $incr, $data_to_exp['user_id']);
				$objPHPExcel->getActiveSheet()->SetCellValue('H' . $incr, $data_to_exp['_refdata']);
                $objPHPExcel->getActiveSheet()->SetCellValue('I' . $incr, $data_to_exp['price']);
                $objPHPExcel->getActiveSheet()->SetCellValue('J' . $incr, $data_to_exp['order-date']);
                $objPHPExcel->getActiveSheet()->SetCellValue('K' . $incr, $data_to_exp['age']);
                $objPHPExcel->getActiveSheet()->SetCellValue('L' . $incr, $data_to_exp['grade']);
                $objPHPExcel->getActiveSheet()->SetCellValue('M' . $incr, $data_to_exp['Emergency Contact Name']);
                $objPHPExcel->getActiveSheet()->SetCellValue('N' . $incr, $data_to_exp['Emergency contact phone']);
				$objPHPExcel->getActiveSheet()->SetCellValue('O' . $incr, $data_to_exp['town_ship_readington']);
                $objPHPExcel->getActiveSheet()->SetCellValue('P' . $incr, $data_to_exp['Please list medical concerns,food allergies or other know allergies instructors need to know']);
				$objPHPExcel->getActiveSheet()->SetCellValue('Q' . $incr, $data_to_exp['_tmcartepo_data']);				
				$objPHPExcel->getActiveSheet()->SetCellValue('R' . $incr, $data_to_exp['dob']);				
                $objPHPExcel->getActiveSheet()->SetCellValue('S' . $incr, $data_to_exp['gender']);
                $objPHPExcel->getActiveSheet()->SetCellValue('T' . $incr, $data_to_exp['Home address']);
                $objPHPExcel->getActiveSheet()->SetCellValue('U' . $incr, $data_to_exp['Home phone']);
                $objPHPExcel->getActiveSheet()->SetCellValue('V' . $incr, $data_to_exp['Parent Name']);
                $objPHPExcel->getActiveSheet()->SetCellValue('W' . $incr, $data_to_exp['order-id']);
				$objPHPExcel->getActiveSheet()->SetCellValue('X' . $incr, $data_to_exp['product-id']);
                $objPHPExcel->getActiveSheet()->SetCellValue('Y' . $incr, $data_to_exp['Extra options']);
                $objPHPExcel->getActiveSheet()->SetCellValue('Z' . $incr, $data_to_exp['AdditionalOptions']['T Shirt size']);	
				
                $incr++;
            }
        }
    }
    if ($type == 'class_detailed') {
        if ($data && is_array($data)) {
            $get_headers = array_keys($data[0]['attendee_meta']);
            $makeABSarray = array('D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            $objPHPExcel->getActiveSheet()->SetCellValue('A' . '1', 'Order Date');
            $objPHPExcel->getActiveSheet()->SetCellValue('B' . '1', 'Parent Email');
            $objPHPExcel->getActiveSheet()->SetCellValue('C' . '1', 'Primary Phone');
            
            foreach ($get_headers as $k => $headers) {
                $objPHPExcel->getActiveSheet()->SetCellValue($makeABSarray[$k] . '1', ucwords( str_replace('-',' ', $headers)  ) );
            }
            $incr = 2;
            foreach ($data as $data_to_exp) {
				$objPHPExcel->getActiveSheet()->SetCellValue('A' . $incr, $data_to_exp['order_date']);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $incr, $data_to_exp['purchaser_email']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $incr, $data_to_exp['phone']);
                $k = 0;
                foreach($get_headers as $key) {
                    if (is_array($data_to_exp['attendee_meta'][$key]['value'])) {
                        $variableKey = array_keys($data_to_exp['attendee_meta'][$key]['value']);
                        $objPHPExcel->getActiveSheet()->SetCellValue($makeABSarray[$k] . $incr, $data_to_exp['attendee_meta'][$key]['value'][$variableKey[0]]);
                    } else {
                        $objPHPExcel->getActiveSheet()->SetCellValue($makeABSarray[$k] . $incr, $data_to_exp['attendee_meta'][$key]['value']);
                    }
                    $k++;
                }

                // $objPHPExcel->getActiveSheet()->SetCellValue('C' . $incr, $data_to_exp['lname']);
                // $objPHPExcel->getActiveSheet()->SetCellValue('D' . $incr, $data_to_exp['contact Number']);
                // $objPHPExcel->getActiveSheet()->SetCellValue('E' . $incr, $data_to_exp['Payment mode']);
                // $objPHPExcel->getActiveSheet()->SetCellValue('F' . $incr, $data_to_exp['user_email']);
                // $objPHPExcel->getActiveSheet()->SetCellValue('G' . $incr, $data_to_exp['user_id']);
				// $objPHPExcel->getActiveSheet()->SetCellValue('H' . $incr, $data_to_exp['_refdata']);
                // $objPHPExcel->getActiveSheet()->SetCellValue('I' . $incr, $data_to_exp['price']);
                // $objPHPExcel->getActiveSheet()->SetCellValue('J' . $incr, $data_to_exp['order-date']);
                // $objPHPExcel->getActiveSheet()->SetCellValue('K' . $incr, $data_to_exp['age']);
                // $objPHPExcel->getActiveSheet()->SetCellValue('L' . $incr, $data_to_exp['grade']);
                // $objPHPExcel->getActiveSheet()->SetCellValue('M' . $incr, $data_to_exp['Emergency Contact Name']);
                // $objPHPExcel->getActiveSheet()->SetCellValue('N' . $incr, $data_to_exp['Emergency contact phone']);
				// $objPHPExcel->getActiveSheet()->SetCellValue('O' . $incr, $data_to_exp['town_ship_readington']);
                // $objPHPExcel->getActiveSheet()->SetCellValue('P' . $incr, $data_to_exp['Please list medical concerns,food allergies or other know allergies instructors need to know']);
				// $objPHPExcel->getActiveSheet()->SetCellValue('Q' . $incr, $data_to_exp['_tmcartepo_data']);				
				// $objPHPExcel->getActiveSheet()->SetCellValue('R' . $incr, $data_to_exp['dob']);				
                // $objPHPExcel->getActiveSheet()->SetCellValue('S' . $incr, $data_to_exp['gender']);
                // $objPHPExcel->getActiveSheet()->SetCellValue('T' . $incr, $data_to_exp['Home address']);
                // $objPHPExcel->getActiveSheet()->SetCellValue('U' . $incr, $data_to_exp['Home phone']);
                // $objPHPExcel->getActiveSheet()->SetCellValue('V' . $incr, $data_to_exp['Parent Name']);
                // $objPHPExcel->getActiveSheet()->SetCellValue('W' . $incr, $data_to_exp['order-id']);
				// $objPHPExcel->getActiveSheet()->SetCellValue('X' . $incr, $data_to_exp['product-id']);
                // $objPHPExcel->getActiveSheet()->SetCellValue('Y' . $incr, $data_to_exp['Extra options']);
                // $objPHPExcel->getActiveSheet()->SetCellValue('Z' . $incr, $data_to_exp['AdditionalOptions']['T Shirt size']);	
				
                $incr++;
            }
        }
    }
    if ($type == "daily") {
        if ($data && is_array($data)) {
            $get_headers = array_keys($data[0]);
            $makeABSarray = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S');
            foreach ($get_headers as $k => $headers) {
                $objPHPExcel->getActiveSheet()->SetCellValue($makeABSarray[$k] . '1', $headers);
            }
            $incr = 2;
            foreach ($data as $data_to_exp) {
                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $incr, $data_to_exp['Category']);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $incr, $data_to_exp['name']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $incr, $data_to_exp['PaidByCheck']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $incr, $data_to_exp['amountBYcheck']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E' . $incr, $data_to_exp['amountBYcc']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F' . $incr, $data_to_exp['PaidByCC']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G' . $incr, $data_to_exp['Qty']);
                $objPHPExcel->getActiveSheet()->SetCellValue('H' . $incr, $data_to_exp['totalSale']);
                $incr++;
            }
        }
    }
    if ($type == "daily-all") {
        if ($data && is_array($data)) {
            $get_headers = array_keys($data[0]);
            $makeABSarray = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S');
            foreach ($get_headers as $k => $headers) {
                $objPHPExcel->getActiveSheet()->SetCellValue($makeABSarray[$k] . '1', $headers);
            }
            $incr = 2;
            foreach ($data as $data_to_exp) {
                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $incr, $data_to_exp['product']);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $incr, $data_to_exp['category']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $incr, $data_to_exp['Participant Name']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $incr, $data_to_exp['gross']);
                $incr++;
            }
        }
    }
    if ($type == "dogpark") {
        if ($data && is_array($data)) {
            $get_headers = array_keys($data[0]);
            $makeABSarray = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
            foreach ($get_headers as $k => $headers) {
                $objPHPExcel->getActiveSheet()->SetCellValue($makeABSarray[$k] . '1', $headers);
            }
            $incr = 2;
            foreach ($data as $data_to_exp) {
                $dog = unserialize(unserialize($data_to_exp['dog_owner_doginfo']));
                $dogval = '';
                if (is_array($dog) && count($dog) > 0) {
                    foreach ($dog as $key => $dogVal) {
                        foreach ($dogVal as $key1 => $dogVal1) {
                            if ($key1 == 9) {
                                $dogval .= $dogVal1[0] . ' Year, ';
                            } else {
                                $dogval .= $dogVal1[0] . ', ';
                            }
                        }
                        $dogval .= '; ';
                    }
                }
                $objPHPExcel->getActiveSheet()->SetCellValue('A' . $incr, $data_to_exp['dog_owner_name']);
                $objPHPExcel->getActiveSheet()->SetCellValue('B' . $incr, $data_to_exp['dog_owner_email']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C' . $incr, $data_to_exp['Participant']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D' . $incr, $data_to_exp['dog_owner_address']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E' . $incr, $data_to_exp['dog_owner_city']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F' . $incr, $data_to_exp['dog_owner_state']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G' . $incr, $data_to_exp['dog_owner_zip']);
                $objPHPExcel->getActiveSheet()->SetCellValue('H' . $incr, $data_to_exp['dog_owner_municipality_name']);
                $objPHPExcel->getActiveSheet()->SetCellValue('I' . $incr, $dogval);
                $objPHPExcel->getActiveSheet()->SetCellValue('J' . $incr, $data_to_exp['order-id']);
                $incr++;
            }
        }
    }
    $objPHPExcel->getActiveSheet()->setTitle('Simple');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save(get_template_directory() . '/ReadingtonReport.xlsx');
}