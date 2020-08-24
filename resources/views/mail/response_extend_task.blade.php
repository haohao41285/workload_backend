<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
	<h3>Phản hồi yêu cầu gia hạn</h3>
	<p>Trạng thái phản hồi : {{ $status }}</p>
	<p>Tên task : {{ $task_name }}</p>
	<p>Ngày hết hạn cũ: {{ $old_expired }}</p>
	<p>Yêu cầu gia hạn: {{ $requested_expired }}</p>
	<p>Ngày hết hạn được chấp nhận: {{ $response_expired }}</p>
	<p>Note: {{ $note }}</p>
</body>
</html>