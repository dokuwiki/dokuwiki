<?php declare(strict_types=1);

namespace DOMWrap;

use DOMWrap\Traits\{
    CommonTrait,
    TraversalTrait,
    ManipulationTrait
};

/**
 * Document Node
 *
 * @package DOMWrap
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3 Clause
 */
class Document extends \DOMDocument
{
    use CommonTrait;
    use TraversalTrait;
    use ManipulationTrait;

    /** @var int */
    protected $libxmlOptions = 0;

    /** @var string|null */
    protected $documentEncoding = null;

    public function __construct(string $version = '1.0', string $encoding = 'UTF-8') {
        parent::__construct($version, $encoding);

        $this->registerNodeClass('DOMText', 'DOMWrap\\Text');
        $this->registerNodeClass('DOMElement', 'DOMWrap\\Element');
        $this->registerNodeClass('DOMComment', 'DOMWrap\\Comment');
        $this->registerNodeClass('DOMDocument', 'DOMWrap\\Document');
        $this->registerNodeClass('DOMDocumentType', 'DOMWrap\\DocumentType');
        $this->registerNodeClass('DOMProcessingInstruction', 'DOMWrap\\ProcessingInstruction');
    }

    /**
     * Set libxml options.
     *
     * Multiple values must use bitwise OR.
     * eg: LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
     *
     * @link http://php.net/manual/en/libxml.constants.php
     *
     * @param int $libxmlOptions
     */
    public function setLibxmlOptions(int $libxmlOptions): void {
        $this->libxmlOptions = $libxmlOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function document(): ?\DOMDocument {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function collection(): NodeList {
        return $this->newNodeList([$this]);
    }

    /**
     * {@inheritdoc}
     */
    public function result(NodeList $nodeList) {
        if ($nodeList->count()) {
            return $nodeList->first();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function parent() {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function parents() {
        return $this->newNodeList();
    }

    /**
     * {@inheritdoc}
     */
    public function substituteWith($newNode): self {
        $this->replaceChild($newNode, $this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function _clone() {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getHtml(): string {
        return $this->getOuterHtml();
    }

    /**
     * {@inheritdoc}
     */
    public function setHtml($html): self {
        if (!is_string($html) || trim($html) == '') {
            return $this;
        }

        $internalErrors = libxml_use_internal_errors(true);
        if (\PHP_VERSION_ID < 80000) {
            $disableEntities = libxml_disable_entity_loader(true);
            $this->composeXmlNode($html);
            libxml_use_internal_errors($internalErrors);
            libxml_disable_entity_loader($disableEntities);
        } else {
            $this->composeXmlNode($html);
            libxml_use_internal_errors($internalErrors);
        }

        return $this;
    }

    /**
     * @param string $html
     * @param int $options
     *
     * @return bool
     */
    public function loadHTML($html, $options = 0): bool {
        // Fix LibXML's crazy-ness RE root nodes
        // While importing HTML using the LIBXML_HTML_NOIMPLIED option LibXML insists
        //  on having one root node. All subsequent nodes are appended to this first node.
        // To counter this we will create a fake element, allow LibXML to 'do its thing'
        //  then undo it by taking the contents of the fake element, placing it back into
        //  the root and then remove our fake element.
        if ($options & LIBXML_HTML_NOIMPLIED) {
            $html = '<domwrap></domwrap>' . $html;
        }

        $html = '<?xml encoding="' . ($this->getEncoding() ?? 'UTF-8') . '">' . $html;

        $result = parent::loadHTML($html, $options);

        // Do our re-shuffling of nodes.
        if ($this->libxmlOptions & LIBXML_HTML_NOIMPLIED) {
            $this->children()->first()->contents()->each(function($node){
                $this->appendWith($node);
            });

            $this->removeChild($this->children()->first());
        }

        return $result;
    }

    /*
     * @param $encoding string|null
     */
    public function setEncoding(string $encoding = null) {
        $this->documentEncoding = $encoding;
    }

    /*
     * @return string|null
     */
    public function getEncoding(): ?string {
        return $this->documentEncoding;
    }

    /*
     * @param $html string
     *
     * @return string|null
     */
    private function getCharset(string $html): ?string {
        $charset = null;

        if (preg_match('@<meta.*?charset=["\']?([^"\'\s>]+)@im', $html, $matches)) {
            $charset = mb_strtoupper($matches[1]);
        }

        return $charset;
    }

    /*
     * @param $html string
     */
    private function detectEncoding(string $html) {
        $charset = $this->getEncoding();

        if (is_null($charset)) {
            $charset = $this->getCharset($html);
        }

        $detectedCharset = mb_detect_encoding($html, mb_detect_order(), true);

        if ($charset === null && $detectedCharset == 'UTF-8') {
            $charset = $detectedCharset;
        }

        $this->setEncoding($charset);
    }

    /*
     * @param $html string
     *
     * @return string
     */
    private function convertToUtf8(string $html): string {
        $charset = $this->getEncoding();

        if ($charset !== null) {
            $html = preg_replace('@(charset=["]?)([^"\s]+)([^"]*["]?)@im', '$1UTF-8$3', $html);
            $mbHasCharset = in_array($charset, array_map('mb_strtoupper', mb_list_encodings()));

            if ($mbHasCharset) {
                $html = mb_convert_encoding($html, 'UTF-8', $charset);

            // Fallback to iconv if available.
            } elseif (extension_loaded('iconv')) {
                $htmlIconv = iconv($charset, 'UTF-8', $html);

                if ($htmlIconv !== false) {
                    $html = $htmlIconv;
                } else {
                    $charset = null;
                }
            }
        }

        if ($charset === null) {
            $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        }

        return $html;
    }

    /**
     * @param $html
     */
    private function composeXmlNode($html)
    {
        $this->detectEncoding($html);

        $html = $this->convertToUtf8($html);

        $this->loadHTML($html, $this->libxmlOptions);

        // Remove <?xml ...> processing instruction.
        $this->contents()->each(function($node) {
            if ($node instanceof ProcessingInstruction && $node->nodeName == 'xml') {
                $node->destroy();
            }
        });
    }
}
