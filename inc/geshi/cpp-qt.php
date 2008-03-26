<?php
/*************************************************************************************
 * cpp.php
 * -------
 * Author: Iulian M
 * Copyright: (c) 2006 Iulian M
 * Release Version: 1.0.7.21
 * Date Started: 2004/09/27
 *
 * C++ language file for GeSHi, with QT extensions.
 *
 * CHANGES
 * -------
 *
 * TODO
 * ----
 *
 *************************************************************************************
 *
 *     This file is part of GeSHi.
 *
 *   GeSHi is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   GeSHi is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with GeSHi; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ************************************************************************************/

$language_data = array (
	'LANG_NAME' => 'C++ (QT)',
	'COMMENT_SINGLE' => array(1 => '//', 2 => '#'),
	'COMMENT_MULTI' => array('/*' => '*/'),
	'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
	'QUOTEMARKS' => array("'", '"'),
	'ESCAPE_CHAR' => '\\',
	'KEYWORDS' => array(
		1 => array(
			'case', 'continue', 'default', 'do', 'else', 'for', 'goto', 'if', 'return',
			'switch', 'while'
			),
		2 => array(
			'NULL', 'false', 'break', 'true', 'enum', 'errno', 'EDOM',
			'ERANGE', 'FLT_RADIX', 'FLT_ROUNDS', 'FLT_DIG', 'DBL_DIG', 'LDBL_DIG',
			'FLT_EPSILON', 'DBL_EPSILON', 'LDBL_EPSILON', 'FLT_MANT_DIG', 'DBL_MANT_DIG',
			'LDBL_MANT_DIG', 'FLT_MAX', 'DBL_MAX', 'LDBL_MAX', 'FLT_MAX_EXP', 'DBL_MAX_EXP',
			'LDBL_MAX_EXP', 'FLT_MIN', 'DBL_MIN', 'LDBL_MIN', 'FLT_MIN_EXP', 'DBL_MIN_EXP',
			'LDBL_MIN_EXP', 'CHAR_BIT', 'CHAR_MAX', 'CHAR_MIN', 'SCHAR_MAX', 'SCHAR_MIN',
			'UCHAR_MAX', 'SHRT_MAX', 'SHRT_MIN', 'USHRT_MAX', 'INT_MAX', 'INT_MIN',
			'UINT_MAX', 'LONG_MAX', 'LONG_MIN', 'ULONG_MAX', 'HUGE_VAL', 'SIGABRT',
			'SIGFPE', 'SIGILL', 'SIGINT', 'SIGSEGV', 'SIGTERM', 'SIG_DFL', 'SIG_ERR',
			'SIG_IGN', 'BUFSIZ', 'EOF', 'FILENAME_MAX', 'FOPEN_MAX', 'L_tmpnam', 'NULL',
			'SEEK_CUR', 'SEEK_END', 'SEEK_SET', 'TMP_MAX', 'stdin', 'stdout', 'stderr',
			'EXIT_FAILURE', 'EXIT_SUCCESS', 'RAND_MAX', 'CLOCKS_PER_SEC',
			'virtual', 'public', 'private', 'protected', 'template', 'using', 'namespace',
			'try', 'catch', 'inline', 'dynamic_cast', 'const_cast', 'reinterpret_cast',
			'static_cast', 'explicit', 'friend', 'wchar_t', 'typename', 'typeid', 'class' ,
			'foreach','connect', 'Q_OBJECT' , 'slots' , 'signals'
			),
		3 => array(
			'cin', 'cerr', 'clog', 'cout', 'delete', 'new', 'this',
			'printf', 'fprintf', 'snprintf', 'sprintf', 'assert',
			'isalnum', 'isalpha', 'isdigit', 'iscntrl', 'isgraph', 'islower', 'isprint',
			'ispunct', 'isspace', 'ispunct', 'isupper', 'isxdigit', 'tolower', 'toupper',
			'exp', 'log', 'log10', 'pow', 'sqrt', 'ceil', 'floor', 'fabs', 'ldexp',
			'frexp', 'modf', 'fmod', 'sin', 'cos', 'tan', 'asin', 'acos', 'atan', 'atan2',
			'sinh', 'cosh', 'tanh', 'setjmp', 'longjmp', 'asin', 'acos', 'atan', 'atan2',
			'va_start', 'va_arg', 'va_end', 'offsetof', 'sizeof', 'fopen', 'freopen',
			'fflush', 'fclose', 'remove', 'rename', 'tmpfile', 'tmpname', 'setvbuf',
			'setbuf', 'vfprintf', 'vprintf', 'vsprintf', 'fscanf', 'scanf', 'sscanf',
			'fgetc', 'fgets', 'fputc', 'fputs', 'getc', 'getchar', 'gets', 'putc',
			'putchar', 'puts', 'ungetc', 'fread', 'fwrite', 'fseek', 'ftell', 'rewind',
			'fgetpos', 'fsetpos', 'clearerr', 'feof', 'ferror', 'perror', 'abs', 'labs',
			'div', 'ldiv', 'atof', 'atoi', 'atol', 'strtod', 'strtol', 'strtoul', 'calloc',
			'malloc', 'realloc', 'free', 'abort', 'exit', 'atexit', 'system', 'getenv',
			'bsearch', 'qsort', 'rand', 'srand', 'strcpy', 'strncpy', 'strcat', 'strncat',
			'strcmp', 'strncmp', 'strcoll', 'strchr', 'strrchr', 'strspn', 'strcspn',
			'strpbrk', 'strstr', 'strlen', 'strerror', 'strtok', 'strxfrm', 'memcpy',
			'memmove', 'memcmp', 'memchr', 'memset', 'clock', 'time', 'difftime', 'mktime',
			'asctime', 'ctime', 'gmtime', 'localtime', 'strftime'
			),
		4 => array(
			'auto', 'bool', 'char', 'const', 'double', 'float', 'int', 'long', 'longint',
			'register', 'short', 'shortint', 'signed', 'static', 'struct',
			'typedef', 'union', 'unsigned', 'void', 'volatile', 'extern', 'jmp_buf',
			'signal', 'raise', 'va_list', 'ptrdiff_t', 'size_t', 'FILE', 'fpos_t',
			'div_t', 'ldiv_t', 'clock_t', 'time_t', 'tm',
			),
		5 => array(
			'QAbstractButton','QDir','QIntValidator','QRegExpValidator','QTabWidget','QAbstractEventDispatcher',
			'QDirectPainter','QIODevice','QRegion','QTcpServer','QAbstractExtensionFactory','QDirModel',
			'QItemDelegate','QResizeEvent','QTcpSocket','QAbstractExtensionManager','QDockWidget',
			'QItemEditorCreatorBase','QResource','QTemporaryFile','QAbstractFileEngine','QDomAttr',
			'QItemEditorFactory','QRubberBand','QTestEventList','QAbstractFileEngineHandler','QDomCDATASection',
			'QItemSelection','QScreen','QTextBlock','QAbstractFormBuilder','QDomCharacterData','QItemSelectionModel',
			'QScreenCursor','QTextBlockFormat','QAbstractGraphicsShapeItem','QDomComment','QItemSelectionRange',
			'QScreenDriverFactory','QTextBlockGroup','QAbstractItemDelegate','QDomDocument','QKbdDriverFactory',
			'QScreenDriverPlugin','QTextBlockUserData','QAbstractItemModel','QDomDocumentFragment','QKbdDriverPlugin',
			'QScrollArea','QTextBrowser','QAbstractItemView','QDomDocumentType','QKeyEvent','QScrollBar',
			'QTextCharFormat','QAbstractListModel','QDomElement','QKeySequence','QSemaphore','QTextCodec',
			'QAbstractPrintDialog','QDomEntity','QLabel','QSessionManager','QTextCodecPlugin','QAbstractProxyModel',
			'QDomEntityReference','QLatin1Char','QSet','QTextCursor','QAbstractScrollArea','QDomImplementation',
			'QLatin1String','QSetIterator','QTextDecoder','QAbstractSlider','QDomNamedNodeMap','QLayout','QSettings',
			'QTextDocument','QAbstractSocket','QDomNode','QLayoutItem','QSharedData','QTextDocumentFragment',
			'QAbstractSpinBox','QDomNodeList','QLCDNumber','QSharedDataPointer','QTextEdit','QAbstractTableModel',
			'QDomNotation','QLibrary','QShortcut','QTextEncoder','QAbstractTextDocumentLayout',
			'QDomProcessingInstruction','QLibraryInfo','QShortcutEvent','QTextFormat','QAccessible','QDomText',
			'QLine','QShowEvent','QTextFragment','QAccessibleBridge','QDoubleSpinBox','QLinearGradient',
			'QSignalMapper','QTextFrame','QAccessibleBridgePlugin','QDoubleValidator','QLineEdit','QSignalSpy',
			'QTextFrameFormat','QAccessibleEvent','QDrag','QLineF','QSize','QTextImageFormat','QAccessibleInterface',
			'QDragEnterEvent','QLinkedList','QSizeF','QTextInlineObject','QAccessibleObject','QDragLeaveEvent',
			'QLinkedListIterator','QSizeGrip','QTextLayout','QAccessiblePlugin','QDragMoveEvent','QLinuxFbScreen',
			'QSizePolicy','QTextLength','QAccessibleWidget','QDropEvent','QList','QSlider','QTextLine','QAction',
			'QDynamicPropertyChangeEvent','QListIterator','QSocketNotifier','QTextList','QActionEvent','QErrorMessage',
			'QListView','QSortFilterProxyModel','QTextListFormat','QActionGroup','QEvent','QListWidget','QSound',
			'QTextObject','QApplication','QEventLoop','QListWidgetItem','QSpacerItem','QTextOption','QAssistantClient',
			'QExtensionFactory','QLocale','QSpinBox','QTextStream','QAxAggregated','QExtensionManager',
			'QMacPasteboardMime','QSplashScreen','QTextTable','QAxBase','QFile','QMacStyle','QSplitter',
			'QTextTableCell','QAxBindable','QFileDialog','QMainWindow','QSplitterHandle','QTextTableFormat',
			'QAxFactory','QFileIconProvider','QMap','QSqlDatabase','QThread','QAxObject','QFileInfo','QMapIterator',
			'QSqlDriver','QThreadStorage','QAxScript','QFileOpenEvent','QMatrix','QSqlDriverCreator','QTime',
			'QAxScriptEngine','QFileSystemWatcher','QMenu','QSqlDriverCreatorBase','QTimeEdit','QAxScriptManager',
			'QFlag','QMenuBar','QSqlDriverPlugin','QTimeLine','QAxWidget','QFlags','QMessageBox','QSqlError','QTimer',
			'QBasicTimer','QFocusEvent','QMetaClassInfo','QSqlField','QTimerEvent','QBitArray','QFocusFrame',
			'QMetaEnum','QSqlIndex','QToolBar','QBitmap','QFont','QMetaMethod','QSqlQuery','QToolBox','QBoxLayout',
			'QFontComboBox','QMetaObject','QSqlQueryModel','QToolButton','QBrush','QFontDatabase','QMetaProperty',
			'QSqlRecord','QToolTip','QBuffer','QFontDialog','QMetaType','QSqlRelation','QTransformedScreen',
			'QButtonGroup','QFontInfo','QMimeData','QSqlRelationalDelegate','QTranslator','QByteArray','QFontMetrics',
			'QMimeSource','QSqlRelationalTableModel','QTreeView','QByteArrayMatcher','QFontMetricsF','QModelIndex',
			'QSqlResult','QTreeWidget','QCache','QFormBuilder','QMotifStyle','QSqlTableModel','QTreeWidgetItem',
			'QCalendarWidget','QFrame','QMouseDriverFactory','QStack','QTreeWidgetItemIterator','QCDEStyle',
			'QFSFileEngine','QMouseDriverPlugin','QStackedLayout','QUdpSocket','QChar','QFtp','QMouseEvent',
			'QStackedWidget','QUiLoader','QCheckBox','QGenericArgument','QMoveEvent','QStandardItem','QUndoCommand',
			'QChildEvent','QGenericReturnArgument','QMovie','QStandardItemEditorCreator','QUndoGroup',
			'QCleanlooksStyle','QGLColormap','QMultiHash','QStandardItemModel','QUndoStack','QClipboard',
			'QGLContext','QMultiMap','QStatusBar','QUndoView','QCloseEvent','QGLFormat','QMutableHashIterator',
			'QStatusTipEvent','QUrl','QColor','QGLFramebufferObject','QMutableLinkedListIterator','QString',
			'QUrlInfo','QColorDialog','QGLPixelBuffer','QMutableListIterator','QStringList','QUuid','QColormap',
			'QGLWidget','QMutableMapIterator','QStringListModel','QValidator','QComboBox','QGradient',
			'QMutableSetIterator','QStringMatcher','QVariant','QCommonStyle','QGraphicsEllipseItem',
			'QMutableVectorIterator','QStyle','QVarLengthArray','QCompleter','QGraphicsItem','QMutex',
			'QStyleFactory','QVBoxLayout','QConicalGradient','QGraphicsItemAnimation','QMutexLocker',
			'QStyleHintReturn','QVector','QContextMenuEvent','QGraphicsItemGroup','QNetworkAddressEntry',
			'QStyleHintReturnMask','QVectorIterator','QCopChannel','QGraphicsLineItem','QNetworkInterface',
			'QStyleOption','QVFbScreen','QCoreApplication','QGraphicsPathItem','QNetworkProxy','QStyleOptionButton',
			'QVNCScreen','QCursor','QGraphicsPixmapItem','QObject','QStyleOptionComboBox','QWaitCondition',
			'QCustomRasterPaintDevice','QGraphicsPolygonItem','QObjectCleanupHandler','QStyleOptionComplex',
			'QWhatsThis','QDataStream','QGraphicsRectItem','QPageSetupDialog','QStyleOptionDockWidget',
			'QWhatsThisClickedEvent','QDataWidgetMapper','QGraphicsScene','QPaintDevice','QStyleOptionFocusRect',
			'QWheelEvent','QDate','QGraphicsSceneContextMenuEvent','QPaintEngine','QStyleOptionFrame','QWidget',
			'QDateEdit','QGraphicsSceneEvent','QPaintEngineState','QStyleOptionFrameV2','QWidgetAction','QDateTime',
			'QGraphicsSceneHoverEvent','QPainter','QStyleOptionGraphicsItem','QWidgetItem','QDateTimeEdit',
			'QGraphicsSceneMouseEvent','QPainterPath','QStyleOptionGroupBox','QWindowsMime','QDBusAbstractAdaptor',
			'QGraphicsSceneWheelEvent','QPainterPathStroker','QStyleOptionHeader','QWindowsStyle',
			'QDBusAbstractInterface','QGraphicsSimpleTextItem','QPaintEvent','QStyleOptionMenuItem',
			'QWindowStateChangeEvent','QDBusArgument','QGraphicsSvgItem','QPair','QStyleOptionProgressBar',
			'QWindowsXPStyle','QDBusConnection','QGraphicsTextItem','QPalette','QStyleOptionProgressBarV2',
			'QWorkspace','QDBusConnectionInterface','QGraphicsView','QPen','QStyleOptionQ3DockWindow','QWriteLocker',
			'QDBusError','QGridLayout','QPersistentModelIndex','QStyleOptionQ3ListView','QWSCalibratedMouseHandler',
			'QDBusInterface','QGroupBox','QPicture','QStyleOptionQ3ListViewItem','QWSClient','QDBusMessage','QHash',
			'QPictureFormatPlugin','QStyleOptionRubberBand','QWSEmbedWidget','QDBusObjectPath','QHashIterator',
			'QPictureIO','QStyleOptionSizeGrip','QWSEvent','QDBusReply','QHBoxLayout','QPixmap','QStyleOptionSlider',
			'QWSInputMethod','QDBusServer','QHeaderView','QPixmapCache','QStyleOptionSpinBox','QWSKeyboardHandler',
			'QDBusSignature','QHelpEvent','QPlastiqueStyle','QStyleOptionTab','QWSMouseHandler','QDBusVariant',
			'QHideEvent','QPluginLoader','QStyleOptionTabBarBase','QWSPointerCalibrationData','QDecoration',
			'QHostAddress','QPoint','QStyleOptionTabV2','QWSScreenSaver','QDecorationFactory','QHostInfo','QPointer',
			'QStyleOptionTabWidgetFrame','QWSServer','QDecorationPlugin','QHoverEvent','QPointF','QStyleOptionTitleBar',
			'QWSTslibMouseHandler','QDesignerActionEditorInterface','QHttp','QPolygon','QStyleOptionToolBar','QWSWindow',
			'QDesignerContainerExtension','QHttpHeader','QPolygonF','QStyleOptionToolBox','QWSWindowSurface',
			'QDesignerCustomWidgetCollectionInterface','QHttpRequestHeader','QPrintDialog','QStyleOptionToolButton',
			'QX11EmbedContainer','QDesignerCustomWidgetInterface','QHttpResponseHeader','QPrintEngine',
			'QStyleOptionViewItem','QX11EmbedWidget','QDesignerFormEditorInterface','QIcon','QPrinter',
			'QStyleOptionViewItemV2','QX11Info','QDesignerFormWindowCursorInterface','QIconDragEvent','QProcess',
			'QStylePainter','QXmlAttributes','QDesignerFormWindowInterface','QIconEngine','QProgressBar',
			'QStylePlugin','QXmlContentHandler','QDesignerFormWindowManagerInterface','QIconEnginePlugin',
			'QProgressDialog','QSvgRenderer','QXmlDeclHandler','QDesignerMemberSheetExtension','QImage',
			'QProxyModel','QSvgWidget','QXmlDefaultHandler','QDesignerObjectInspectorInterface','QImageIOHandler',
			'QPushButton','QSyntaxHighlighter','QXmlDTDHandler','QDesignerPropertyEditorInterface','QImageIOPlugin',
			'QQueue','QSysInfo','QXmlEntityResolver','QDesignerPropertySheetExtension','QImageReader','QRadialGradient',
			'QSystemLocale','QXmlErrorHandler','QDesignerTaskMenuExtension','QImageWriter','QRadioButton',
			'QSystemTrayIcon','QXmlInputSource','QDesignerWidgetBoxInterface','QInputContext','QRasterPaintEngine',
			'QTabBar','QXmlLexicalHandler','QDesktopServices','QInputContextFactory','QReadLocker','QTabletEvent',
			'QXmlLocator','QDesktopWidget','QInputContextPlugin','QReadWriteLock','QTableView','QXmlNamespaceSupport',
			'QDial','QInputDialog','QRect','QTableWidget','QXmlParseException','QDialog','QInputEvent','QRectF',
			'QTableWidgetItem','QXmlReader','QDialogButtonBox','QInputMethodEvent','QRegExp',
			'QTableWidgetSelectionRange','QXmlSimpleReader'
			)
		),
	'SYMBOLS' => array(
		'(', ')', '{', '}', '[', ']', '=', '+', '-', '*', '/', '!', '%', '^', '&', ':'
		),
	'CASE_SENSITIVE' => array(
		GESHI_COMMENTS => true,
		1 => false,
		2 => false,
		3 => false,
		4 => false,
		5 => true,
		),
	'STYLES' => array(
		'KEYWORDS' => array(
			1 => 'color: #0000ff;',
			2 => 'color: #0000ff;',
			3 => 'color: #0000dd;',
			4 => 'color: #0000ff;',
			5 => 'color: #0000ee;'
			),
		'COMMENTS' => array(
			1 => 'color: #ff0000;',
			2 => 'color: #339900;',
			'MULTI' => 'color: #ff0000; font-style: italic;'
			),
		'ESCAPE_CHAR' => array(
			0 => 'color: #666666; font-weight: bold;'
			),
		'BRACKETS' => array(
			0 => 'color: #000000;'
			),
		'STRINGS' => array(
			0 => 'color: #666666;'
			),
		'NUMBERS' => array(
			0 => 'color: #0000dd;'
			),
		'METHODS' => array(
			1 => 'color: #00eeff;',
			2 => 'color: #00eeff;'
			),
		'SYMBOLS' => array(
			0 => 'color: #000000;'
			),
		'REGEXPS' => array(
			),
		'SCRIPT' => array(
			)
		),
	'URLS' => array(
		1 => '',
		2 => '',
		3 => '',
		4 => '',
		5 => 'http://doc.trolltech.com/4.2/{FNAME}.html'
		),
	'OOLANG' => true,
	'OBJECT_SPLITTERS' => array(
		1 => '.',
		2 => '::'
		),
	'REGEXPS' => array(
		),
	'STRICT_MODE_APPLIES' => GESHI_NEVER,
	'SCRIPT_DELIMITERS' => array(
		),
	'HIGHLIGHT_STRICT_BLOCK' => array(
        ),
    'TAB_WIDTH' => 4
);

?>
