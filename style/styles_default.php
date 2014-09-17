<?php         
include('../import/settings_default.php');

header('Content-type: text/css');
?>
<!--

div, p, td {
        font-family: <?php echo($font_type); ?>;
        font-size: <?php echo($font_size); ?>;
        color: <?php echo($font_color); ?>;
}


a.normlink:link, a.normlink:visited, a.normlink:active {
        color: <?php echo($font_link_color); ?>;
        text-decoration: none;
        font-family: <?php echo($font_type); ?>;
        border-bottom-width: 0.05em;
        border-bottom-style: solid;
        border-bottom-color: #CCCCCC;
}
a.normlink:hover {
        color: <?php echo($font_link_color); ?>;
        text-decoration: none;
        font-family: <?php echo($font_type); ?>;
        border-bottom-width: 0.05em;
        border-bottom-style: solid;
        border-bottom-color: <?php echo($font_link_color); ?>;
}
.heading {
        font-family: <?php echo($font_type); ?>;
        font-size: 16px;
}
.small {
        font-family: <?php echo($font_type); ?>;
        font-size: 10px;
}
.headingusers {
        font-family: <?php echo($font_type); ?>;
        font-size: 18px;
}
.smallusers {
        font-family: <?php echo($font_type); ?>;
        font-size: 10px;
        color: #CBCBCB;
}
a.tooltip {
        position: relative;
        font-family: <?php echo($font_type); ?>;
        font-size: 10px;
        z-index: 100;
        color: #000000;
        text-decoration: none;
        border-bottom-width: 0.05em;
        border-bottom-style: dashed;
        border-bottom-color: #CCCCCC;
}
a.tooltip:hover {
        z-index: 150;
        background-color: #FFFFFF;
}
a.tooltip span {
        display: none
}
a.tooltip:hover span {
    display: block;
    position: absolute;
    top: 15px;
        left: -100px;
        width: 175px;
        padding: 5px;
        margin: 10px;
    border: 1px dashed #339;
    background-color: #E8EAFC;
        color: #000000;
    text-align: center
}

//-->
