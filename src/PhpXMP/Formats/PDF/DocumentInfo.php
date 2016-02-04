<?php
namespace RsnSavant\Format\PDF;


class DocumentInfo
{
    const TRAPPED_UNKNOWN = "unknown";

    private $title;
    private $author;
    private $subject;
    private $keywords;
    private $creator;
    private $producer;
    private $creationDate;
    private $modDate;
    private $trapped = self::TRAPPED_UNKNOWN;

}