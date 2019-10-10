<?php

namespace dokuwiki;

/**
 * Class Draft
 *
 * @package dokuwiki
 */
class Draft
{

    protected $errors = [];
    protected $cname;
    protected $id;
    protected $client;

    /**
     * Draft constructor.
     *
     * @param string $ID the page id for this draft
     * @param string $client the client identification (username or ip or similar) for this draft
     */
    public function __construct($ID, $client)
    {
        $this->id = $ID;
        $this->client = $client;
        $this->cname = getCacheName($client.$ID, '.draft');
        if(file_exists($this->cname) && file_exists(wikiFN($ID))) {
            if (filemtime($this->cname) < filemtime(wikiFN($ID))) {
                // remove stale draft
                $this->deleteDraft();
            }
        }
    }

    /**
     * Get the filename for this draft (whether or not it exists)
     *
     * @return string
     */
    public function getDraftFilename()
    {
        return $this->cname;
    }

    /**
     * Checks if this draft exists on the filesystem
     *
     * @return bool
     */
    public function isDraftAvailable()
    {
        return file_exists($this->cname);
    }

    /**
     * Save a draft of a current edit session
     *
     * The draft will not be saved if
     *   - drafts are deactivated in the config
     *   - or the editarea is empty and there are no event handlers registered
     *   - or the event is prevented
     *
     * @triggers DRAFT_SAVE
     *
     * @return bool whether has the draft been saved
     */
    public function saveDraft()
    {
        global $INPUT, $INFO, $EVENT_HANDLER, $conf;
        if (!$conf['usedraft']) {
            return false;
        }
        if (!$INPUT->post->has('wikitext') &&
            !$EVENT_HANDLER->hasHandlerForEvent('DRAFT_SAVE')) {
            return false;
        }
        $draft = [
            'id' => $this->id,
            'prefix' => substr($INPUT->post->str('prefix'), 0, -1),
            'text' => $INPUT->post->str('wikitext'),
            'suffix' => $INPUT->post->str('suffix'),
            'date' => $INPUT->post->int('date'),
            'client' => $this->client,
            'cname' => $this->cname,
            'errors' => [],
        ];
        $event = new Extension\Event('DRAFT_SAVE', $draft);
        if ($event->advise_before()) {
            $draft['hasBeenSaved'] = io_saveFile($draft['cname'], serialize($draft));
            if ($draft['hasBeenSaved']) {
                $INFO['draft'] = $draft['cname'];
            }
        } else {
            $draft['hasBeenSaved'] = false;
        }
        $event->advise_after();

        $this->errors = $draft['errors'];

        return $draft['hasBeenSaved'];
    }

    /**
     * Get the text from the draft file
     *
     * @throws \RuntimeException if the draft file doesn't exist
     *
     * @return string
     */
    public function getDraftText()
    {
        if (!file_exists($this->cname)) {
            throw new \RuntimeException(
                "Draft for page $this->id and user $this->client doesn't exist at $this->cname."
            );
        }
        $draft = unserialize(io_readFile($this->cname,false));
        return cleanText(con($draft['prefix'],$draft['text'],$draft['suffix'],true));
    }

    /**
     * Remove the draft from the filesystem
     *
     * Also sets $INFO['draft'] to null
     */
    public function deleteDraft()
    {
        global $INFO;
        @unlink($this->cname);
        $INFO['draft'] = null;
    }

    /**
     * Get a formatted message stating when the draft was saved
     *
     * @return string
     */
    public function getDraftMessage()
    {
        global $lang;
        return $lang['draftdate'] . ' ' . dformat(filemtime($this->cname));
    }

    /**
     * Retrieve the errors that occured when saving the draft
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get the timestamp when this draft was saved
     *
     * @return int
     */
    public function getDraftDate()
    {
        return filemtime($this->cname);
    }
}
