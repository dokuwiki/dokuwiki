<?php

namespace dokuwiki\Parsing\Handler;

/**
 * A ReWriter takes over from the orignal call writer and handles all new calls itself until
 * the process method is called and control is given back to the original writer.
 *
 * @property array[] $calls The list of current calls
 */
interface ReWriterInterface extends CallWriterInterface
{
    /**
     * ReWriterInterface constructor.
     *
     * This rewriter will be registered as the new call writer in the Handler.
     * The original is passed as parameter
     *
     * @param CallWriterInterface $callWriter the original callwriter
     */
    public function __construct(CallWriterInterface $callWriter);

    /**
     * Process any calls that have been added and add them to the
     * original call writer
     *
     * @return CallWriterInterface the orignal call writer
     */
    public function process();

    /**
     * Accessor for this rewriter's original CallWriter
     *
     * @return CallWriterInterface
     */
    public function getCallWriter();
}
