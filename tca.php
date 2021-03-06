<?php
if (!defined ('TYPO3_MODE'))     die ('Access denied.');

$TCA["tx_wtspamshield_log"] = array (
    "ctrl" => $TCA["tx_wtspamshield_log"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "form,errormsg,formvalues,pageid,ip,useragent"
    ),
    "feInterface" => $TCA["tx_wtspamshield_log"]["feInterface"],
    "columns" => array (
        "form" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:wt_spamshield/locallang_db.xml:tx_wtspamshield_log.form",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",    
                "eval" => "required",
            )
        ),
        "errormsg" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:wt_spamshield/locallang_db.xml:tx_wtspamshield_log.errormsg",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",    
                "eval" => "required",
            )
        ),
        "formvalues" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:wt_spamshield/locallang_db.xml:tx_wtspamshield_log.formvalues",        
            "config" => Array (
                "type" => "text",
                "cols" => "30",    
                "rows" => "5",
            )
        ),
        "pageid" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:wt_spamshield/locallang_db.xml:tx_wtspamshield_log.pageid",        
            "config" => Array (
                "type" => "input",    
                "size" => "5",
            )
        ),
        "ip" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:wt_spamshield/locallang_db.xml:tx_wtspamshield_log.ip",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
        "useragent" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:wt_spamshield/locallang_db.xml:tx_wtspamshield_log.useragent",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "form;;;;1-1-1, errormsg, formvalues, pageid, ip, useragent")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);
?>