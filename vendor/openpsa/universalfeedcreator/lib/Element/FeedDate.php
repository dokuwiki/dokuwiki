<?php

/**
 * FeedDate is an internal class that stores a date for a feed or feed item.
 * Usually, you won't need to use this.
 */
class FeedDate
{
    protected $unix;

    /**
     * Creates a new instance of FeedDate representing a given date.
     * Accepts RFC 822, ISO 8601 date formats (or anything that PHP's DateTime
     * can parse, really) as well as unix time stamps.
     *
     * @param mixed $dateString optional the date this FeedDate will represent. If not specified, the current date and
     *                          time is used.
     */
    public function __construct($dateString = "")
    {
        if ($dateString == "") {
            $dateString = date("r");
        }

        if (is_integer($dateString)) {
            $this->unix = $dateString;
        } else {
            try {
                $this->unix = (int) (new Datetime($dateString))->format('U');
            }  catch (Exception $e) {
                $this->unix = 0;
            }
        }
    }

    /**
     * Gets the date stored in this FeedDate as an RFC 822 date.
     *
     * @return string a date in RFC 822 format
     */
    public function rfc822()
    {
        //return gmdate("r",$this->unix);
        $date = gmdate("D, d M Y H:i:s O", $this->unix);

        return $date;
    }

    /**
     * Gets the date stored in this FeedDate as an ISO 8601 date.
     *
     * @return string a date in ISO 8601 format
     */
    public function iso8601()
    {
        $date = gmdate("Y-m-d\TH:i:sP", $this->unix);

        return $date;
    }

    /**
     * Gets the date stored in this FeedDate as unix time stamp.
     *
     * @return int a date as a unix time stamp
     */
    public function unix()
    {
        return $this->unix;
    }
}
