<?php
	require_once (dirname(__FILE__) . '/config/config.php');
	
	// Set the auto loader and open channel to config functions
	$config = new Config();
	$db = $config->database();

	// Load helper class
	$helper = new Helpers();

	// Load Json class
	$jsonObject = new Json();

	echo $jsonObject->json;
	exit;
	$json = json_decode($jsonObject->json);
	
	processJSON($json);

	// You must run a query for each media object and retrieve its insert id, then insert each media asset with that id 
	function processJSON($data)
	{
		global $helper;
		foreach($data AS $mediaKey => $mediaValue)
		{
			$mediaValues = array(
				'license_id' => $data[$mediaKey]['licenseId'],
				'vendor_id' => $data[$mediaKey]['vendorId'],
				'vendor_media_id' => $data[$mediaKey]['vendorMediaId'],
				'description' => isset($data[$mediaKey]['description']) ? $data[$mediaKey]['description'] : '',
				'type' => $data[$mediaKey]['type']
			);
			
			// Set the type to determine what asset table and values are needed
			$type = $data[$mediaKey]['type'];
			
			// Write the media asset to the table
			$result = $db->insert('media',$mediaValues);
			
			// Retrieve the media_id
			$media_id = $result ? $db->lastInsertId() : null;
			
			// Loop through each asset and write them to the appropriate table
			foreach ($data[$mediaKey]['mediaAssets'] AS $assetKey => $value)
			{
				// All assets share these values
				$assetValues = array(
					'media_id' => isset($media_id) ? $media_id : '',
					'url' => isset($data[$mediaKey]['mediaAssets'][$assetKey]['url']) ? $data[$mediaKey]['mediaAssets'][$assetKey]['url'] : '',
					'name' => isset($data[$mediaKey]['mediaAssets'][$assetKey]['name']) ? $data[$mediaKey]['mediaAssets'][$assetKey]['name'] : ''
				);
				
				// Set the appropriate values whether it is an image or a video
				if ($type == 'image' || $type == 'video')
				{
					$assetValues['height'] = isset($data[$mediaKey]['mediaAssets'][$assetKey]['height']) ? $data[$mediaKey]['mediaAssets'][$assetKey]['height'] : '';
					$assetValues['width'] = isset($data[$mediaKey]['mediaAssets'][$assetKey]['width']) ? $data[$mediaKey]['mediaAssets'][$assetKey]['width'] : '';
					
					if ($type == 'image')
					{
						$assetValues['size'] = isset($data[$mediaKey]['mediaAssets'][$assetKey]['size']) ? $data[$mediaKey]['mediaAssets'][$assetKey]['size'] : '';
					}
						else 
						{
							$assetValues['length'] = isset($data[$mediaKey]['mediaAssets'][$assetKey]['length']) ? $data[$mediaKey]['mediaAssets'][$assetKey]['length'] : '';
						}
				}
					// Set the appropriate values for an audio asset
					elseif ($type == 'audio')
					{
						$assetValues['length'] = isset($data[$mediaKey]['mediaAssets'][$assetKey]['length']) ? $data[$mediaKey]['mediaAssets'][$assetKey]['length'] : '';
					}
				
				// Write the asset to the appropriate table
				$assetResult = $db->insert($type . '_asset', $assetValues); 
			}
		}
	}
?>