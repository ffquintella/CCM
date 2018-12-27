<?php

/**
 * Created by Felipe F Quintella.
 * User: felipe.quintella
 * Date: 18/06/13
 * Time: 18:46
 * To change this template use File | Settings | File Templates.
 */
class mailTemplate
{

    private $name, $subject, $header, $body, $images;

    function __construct($name, $body, $subject)
    {
        $this->images = array();
        $this->name = $name;
        $this->body = $body;
        $this->subject = $subject;

        /*
        $header = "From: %from% \r\n";
        $header .= "Reply-To: %to% \r\n";
        //$header .= "CC: susan@example.com\r\n";
        $header .= "MIME-Version: 1.0\r\n";
        //$header .= "tContent-Type: text/html; charset=ISO-8859-1\r\n";
        $header .= "tContent-Type: text/html; charset=UTF-8\r\n";
        */
    }

    public function addImg($path, $name)
    {
        $more = array($name => $path);
        $this->images = array_merge($this->images, $more);
    }

    /**
     * @return mixed
     */
    public function getImgs()
    {
        return $this->images;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return mixed
     */
    public function getHeader()
    {

        return $this->header;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }


}