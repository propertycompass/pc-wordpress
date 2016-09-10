<?php

/**
 * -------------------------------------------------------------------------------------------------------------
 * Listings Controller
 * URL: /listings
 */
class ListingsController {
	/**
	 * For each file in the %wp-uploads%/propertycompass/queue
	 * Trigger the listings import process
	 */
	public function Process() {
		$base = wp_upload_dir();
		$queue_path = $base['basedir'] . '/propertycompass/queue';
		$files = glob($queue_path . '/*.json', GLOB_BRACE);
		foreach($files as $file) {
  			$this->processFile($file);
		}
		//wp_send_json(['success' => true, 'files' => $files]);
		die();
	}

	/**
	 * Saves the incoming JSON body to the propertycompass directory inside the Wordpress uploads folder
	 * URL: /listings/upload
	 */
	public function Upload() {

		$data = file_get_contents('php://input');

		//Validate
		if ($_SERVER["CONTENT_TYPE"] != "application/json")
			wp_send_json_error('status 400: invalid content-type specified');

		if (!$data || empty($data))
			wp_send_json_error('status 400: empty or invalid payload');

		$file_name = 'upload-' . gmdate('YmdHis') . '.json';
		$base = wp_upload_dir();
		$upload_path = $base['basedir'] . '/propertycompass/queue';

		if (!file_exists($upload_path)) {
			wp_mkdir_p($upload_path);
		}

		$full_path = $upload_path . '/' . $file_name;

		if (file_put_contents($full_path, $data, FILE_TEXT )) {
			wp_send_json([
				'success' => true,
				'fileName' => $file_name,
				'fullPath' => $full_path
			]);
		}
	}

	private function processFile($file) {
		$listings = json_decode(file_get_contents($file));
		$importer = new PC_Listing_Importer();

		$isSuccess = true;
		foreach($listings as $listing) {
			print_r(">> IN - " . $listing->id . "\n");

			if (!$importer->import($listing))
				$isSuccess = false;
		}

		$this->completeProcess($file, $isSuccess);
	}

	private function completeProcess($file, $isSuccess) {
		$base = wp_upload_dir();
		$success_path = $base['basedir'] . '/propertycompass/processed';
		$failure_path = $base['basedir'] . '/propertycompass/error';

		if (!file_exists($success_path))
			wp_mkdir_p($success_path);

		if (!file_exists($failure_path))
			wp_mkdir_p($failure_path);



		$filename =  basename($file);
		$full_path = $isSuccess ? $success_path : $failure_path;
		$full_path .= '/' . $filename;

		print_r('Path: ' . $full_path . '\n');
		rename($file, $full_path);
		die;
	}
}