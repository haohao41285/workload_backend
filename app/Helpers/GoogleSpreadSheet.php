<?php
function getClient() {
	$client = new \Google_Client();
	$client->setApplicationName('Contact App');
	$client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
	$client->setAccessType('offline');
	$client->setAuthConfig(public_path('credentials.json'));
	$sheets = new \Google_Service_Sheets($client);
	return $sheets;
}
function appendRow($values) {
	$sheets = getClient();
	$range = "A2:K";
	$body = new \Google_Service_Sheets_ValueRange([
		'values' => $values,
	]);
	$params = [
		'valueInputOption' => 'RAW',
	];
	$result = $sheets->spreadsheets_values->append('1WnJhl20ii9xmL-t0TizFSGNkKks-dBGflaDPO-2QJ9Y', $range, $body, $params);
	// printf("%d cells appended.", $result->getUpdates()->getUpdatedCells());
}
function getAllRows() {
	$sheets = getClient();
	$range = "A:K";
	$result = $sheets->spreadsheets_values->get('1WnJhl20ii9xmL-t0TizFSGNkKks-dBGflaDPO-2QJ9Y', $range);
	return $result;
}
function updateRow($values, $id, $range) {
	$sheets = getClient();
	$spreadsheetId = '1WnJhl20ii9xmL-t0TizFSGNkKks-dBGflaDPO-2QJ9Y';
	$body = new \Google_Service_Sheets_ValueRange([
		'values' => $values,
	]);
	$params = [
		'valueInputOption' => 'RAW',
	];
	$result = $sheets->spreadsheets_values->update(
		$spreadsheetId,
		$range,
		$body,
		$params
	);
}
?>
