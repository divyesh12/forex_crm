<?php
if (!empty($_REQUEST)) {
    echo urldecode($_REQUEST['message']);
}