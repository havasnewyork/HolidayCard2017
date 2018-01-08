<?php

try {

	$conn = new PDO( 'mysql:host=127.0.0.1;dbname=HolidayCard2017', 'root', 'root' );

	$conn->beginTransaction();
	$result  = $conn->query( "SELECT * FROM card WHERE video_url = '' AND status = 0 ORDER BY created_at ASC LIMIT 1 FOR UPDATE",
		PDO::FETCH_OBJ )->fetch();
	$working = $conn->query( sprintf( "UPDATE card SET status=1 WHERE id = %d", $result->id ) );

	sleep( 5 );

	$conn->commit();

	var_dump( $result );

} catch ( Exception $e ) {
	print_r( $e->getMessage() );
}
