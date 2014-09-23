<?php

function encodeXML($data)
{
	return(htmlspecialchars(html_entity_decode($data, ENT_QUOTES, 'UTF-8'), ENT_NOQUOTES, "UTF-8"));
}

?>