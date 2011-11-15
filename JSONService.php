<?php
	require_once('config.php');
	require_once ("database.php");
	
	$db = database();
	
	$json = preg_replace("/\\\\/u", "", $_POST['json']);
	
	$json = json_decode($json,true);

	// Pull the function to be called from the decoded JSON associative array
	$function = $json['function'];
	
	// Pull the assets array from the decoded JSON associative array
	$json = $json['data'];
	
	// Call the function passed through the JSON object
	$function($json);
	
	// You must run a query for each media object and retrieve its insert id, then insert each media asset with that id
	function saveMediaAssets($data)
	{
		global $db;
		foreach ($data AS $mediaKey => $mediaValue)
		{
			// Set the values that will be saved as the media asset
			$mediaValues = array(
					'vendor_id' => isset($data[$mediaKey]['vendorId']) ? $data[$mediaKey]['vendorId'] : '',
					'vendor_media_id' => isset($data[$mediaKey]['vendorMediaId']) ? $data[$mediaKey]['vendorMediaId'] : '',
					'description' => isset($data[$mediaKey]['description']) ? $data[$mediaKey]['description'] : '',
					'name' => isset($data[$mediaKey]['name']) ? $data[$mediaKey]['name'] : ''
			);
			
			// If license ID is set and not empty, add it to the media object values, otherwise let it default
			if (isset($data[$mediaKey]['licenseId']) && !empty($data[$mediaKey]['licenseId']))
			{
				$mediaValues['license_id'] = $data[$mediaKey]['licenseId'];
			}
			
			// If type is set and not empty, add it to the media object values, otherwise let it default
			if (isset($data[$mediaKey]['type']) && !empty($data[$mediaKey]['type']))
			{
				$data[$mediaKey]['type'] = $data[$mediaKey]['type'];
			}

			// Set the asset type to determine what asset table and values are needed
			$type = $data[$mediaKey]['type'];

			// Write the media asset to the table
			$result = $db->insert('media', $mediaValues);

			// Retrieve the media_id
			$media_id = $result ? $db->lastInsertId() : null;

			// Loop through each asset and write them to the appropriate table
			foreach ($data[$mediaKey]['mediaAssets'] AS $assetKey => $value)
			{
				// All assets share these values
				$assetValues = array(
						'media_id' => isset($media_id) ? $media_id : '',
						'url' => isset($data[$mediaKey]['mediaAssets'][$assetKey]['url']) ? $data[$mediaKey]['mediaAssets'][$assetKey]['url'] : '',
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
		
		echo json_encode(array('status' => 'success'));
		exit();
	}

	
	function getAllMediaAssets($type)
	{
		global $db;
		
		$allowed = array('image','video','audio');
		
		if (!in_array($type['type'], $allowed)) 
		{
			echo json_encode(array('status' => 'Requested asset type is not a valid asset type.'));
		}
		
		$result = $db->fetchAll( "
			SELECT *
			FROM " . $type['type'] . "_asset
			WHERE media_id IS NOT NULL
		");
		
		$assets = array();
		foreach ($result AS $row)
		{
			$assets[] = $row;
		}
		
		echo json_encode($assets);
		exit();
	}
	
?>