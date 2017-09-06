<?php

/**
 * @return string
 */
function get_path_to()
{
    $segments = func_get_args();

    $finalIndex = count( $segments ) - 1;
    $finalIndex = $finalIndex < 0 ? 0 : $finalIndex;
    $finalSegment = '';

    $disabledChars = [ '/', '\\', NULL ];
    foreach( $segments as $count => $segment )
    {
        if ( !in_array( $segment, $disabledChars ) ) $finalSegment .= $count > 0 ? trim( $segment, '/' ) : rtrim( $segment, '/' );

        if ( $count != $finalIndex ) $finalSegment .= '/';
    }

    $finalSegment = '/'.ltrim( $finalSegment, '/' );

    return $finalSegment;
}