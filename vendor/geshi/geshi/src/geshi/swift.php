<?php
/*************************************************************************************
 * swift.php
 * ----------
 * Author: Ken Woo (ikenwoo@gmail.com)
 * Copyright: (c) 2015 Ken Woo
 * Release Version: 1.0.9.1
 * Date Started: 2015/05/20
 *
 * Swift language file for GeSHi.
 * To mirror official Apple documentation, set the overall style like so:
 * $geshi->set_overall_style('font-family: Menlo, monospace; font-size: 0.85em; color: #508187;', false);
 *
 * CHANGES
 * -------
 * 2015/05/20
 *  -   First Release
 *
 * TODO (updated 2015/05/20)
 * -------------------------
 *  -   Only added keywords for Swift, Foundation, Core Foundation, Core Graphics, UIKit and AppKit.
 * There are many other frameworks that can be added like SpriteKit, MapKit, HealthKit, etc.
 *  -   Hex regex is a bit wonky when combined with method highlghting and not working
 *      with negative exponents
 *  -   Swift strings can include expressions via "sum is: \( 2 + 3 )" and the expression
 *      shouldn't be highlighted as a string. This isn't supported yet.
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

$language_data = array(
    'LANG_NAME' => 'Swift',
    'COMMENT_SINGLE' => array(1 => '//'),
    'COMMENT_MULTI' => array('/*' => '*/'),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array('"'),
    'ESCAPE_CHAR' => '\\',
    'NUMBERS' => array(
        # Decimals
        0 => '\b[0-9][0-9_]*(\.[0-9][0-9_]*)?([eE][+-]?[0-9][0-9_]*)?\b',
        # Hex
        1 => '\b0x[0-9A-Fa-f][0-9A-Fa-f_]*((\.[0-9A-Fa-f][0-9A-Fa-f_]*)?([pP][+-]?[0-9][0-9_]*))?\b',
        # Octal
        2 => '\b0o[0-7][0-7_]*\b',
        # Binary
        3 => '\b0b[01][01_]*\b'
    ),
    'KEYWORDS' => array(
        /*
        ** Swift Keywords
        */
        1 => array(
            'Protocol', 'Self', 'Type', 'as', 'associativity', 'awillSet', 'break', 'case', 'class',
            'continue', 'convenience', 'default', 'deinit', 'didSet', 'do', 'dynamic', 'dynamicType',
            'else', 'enum', 'extension', 'fallthrough', 'false', 'final', 'for', 'func', 'get', 'if',
            'import', 'in', 'infix', 'init', 'inout', 'internal', 'is', 'lazy', 'left', 'let', 'mutating',
            'nil', 'none', 'nonmutating', 'operator', 'optional', 'override', 'postfix', 'precedence',
            'prefix', 'private', 'protocol', 'public', 'required', 'return', 'right', 'self', 'set',
            'static', 'struct', 'subscript', 'super', 'switch', 'true', 'typealias', 'unowned', 'var',
            'weak', 'where', 'while', '__COLUMN__', '__FILE__', '__FUNCTION__', '__LINE__',
        ),

        /*
        ** Swift Attributes
        */
        2 => array(
            '@availability', '@autoclosure', '@IBAction', '@IBDesignable', '@IBInspectable', '@IBOutlet',
            '@noescape', '@noreturn', '@NSApplicationMain', '@NSCopying', '@NSManaged', '@objc', '@UIApplicationMain'
        ),
        /*
        ** Swift Builtin Functions
        **
        */
        3 => array(
            'abs', 'advance', 'alignof', 'alignofValue', 'assert', 'assertionFailure', 'contains', 'count', 'debugPrint',
            'debugPrintln', 'distance', 'dropFirst', 'dropLast', 'dump', 'enumerate', 'equal', 'extend', 'fatalError',
            'filter', 'find', 'first', 'flatMap', 'getVaList', 'indices', 'insert', 'isEmpty', 'isUniquelyReferenced',
            'isUniquelyReferencedNonObjC', 'join', 'last', 'lexicographicalCompare', 'map', 'max', 'maxElement',
            'min', 'minElement', 'numericCast', 'overlaps', 'partition', 'precondition', 'preconditionFailure',
            'print', 'println', 'reduce', 'reflect', 'removeAll', 'removeAtIndex', 'removeLast', 'removeRange', 'reverse',
            'sizeof', 'sizeofValue', 'sort', 'sorted', 'splice', 'split', 'startsWith', 'stride', 'strideof', 'strideofValue',
            'suffix', 'swap', 'toDebugString', 'toString', 'transcode', 'underestimateCount', 'unsafeAddressOf', 'unsafeBitCast',
            'unsafeDowncast', 'unsafeUnwrap', 'withExtendedLifetime', 'withUnsafeMutablePointer', 'withUnsafeMutablePointers',
            'withUnsafePointer', 'withUnsafePointers', 'withVaList', 'zip'
        ),
        /*
         ** Swift Types
        */
        4 => array(
            'Array', 'ArraySlice', 'AutoreleasingUnsafeMutablePointer', 'BidirectionalReverseView', 'Bit', 'Bool',
            'CFunctionPointer', 'COpaquePointer', 'CVaListPointer', 'Character', 'ClosedInterval', 'CollectionOfOne',
            'ContiguousArray', 'Dictionary', 'DictionaryGenerator', 'DictionaryIndex', 'Double', 'EmptyCollection',
            'EmptyGenerator', 'EnumerateGenerator', 'EnumerateSequence', 'FilterCollectionView', 'FilterCollectionViewIndex',
            'FilterGenerator', 'FilterSequenceView', 'Float', 'Float80', 'FloatingPointClassification', 'GeneratorOf',
            'GeneratorOfOne', 'GeneratorSequence', 'HalfOpenInterval', 'ImplicitlyUnwrappedOptional', 'IndexingGenerator',
            'Int', 'Int16', 'Int32', 'Int64', 'Int8', 'LazyBidirectionalCollection', 'LazyForwardCollection',
            'LazyRandomAccessCollection', 'LazySequence', 'ManagedBuffer', 'ManagedBufferPointer', 'ManagedProtoBuffer',
            'MapCollectionView', 'MapSequenceGenerator', 'MapSequenceView', 'MirrorDisposition', 'NonObjectiveCBase',
            'ObjectIdentifier', 'Optional', 'PermutationGenerator', 'Process', 'QuickLookObject', 'RandomAccessReverseView',
            'Range', 'RangeGenerator', 'RawByte', 'Repeat', 'ReverseBidirectionalIndex', 'ReverseRandomAccessIndex',
            'SequenceOf', 'Set', 'SetGenerator', 'SetIndex', 'SinkOf', 'StaticString', 'StrideThrough', 'StrideThroughGenerator',
            'StrideTo', 'StrideToGenerator', 'String', 'String.Index', 'String.UTF16View', 'String.UTF16View.Index',
            'String.UTF8View', 'String.UTF8View.Index', 'String.UnicodeScalarView', 'String.UnicodeScalarView.Generator',
            'String.UnicodeScalarView.Index', 'UInt', 'UInt16', 'UInt32', 'UInt64', 'UInt8', 'UTF16', 'UTF32', 'UTF8',
            'UnicodeDecodingResult', 'UnicodeScalar', 'UnicodeScalar.UTF16View', 'Unmanaged', 'UnsafeBufferPointer',
            'UnsafeBufferPointerGenerator', 'UnsafeMutableBufferPointer', 'UnsafeMutablePointer', 'UnsafePointer',
            'VaListBuilder', 'Zip2', 'ZipGenerator2'
        ),
        /*
        ** Swift Type Aliases
        */
        5 => array(
            'Any', 'AnyClass', 'CBool', 'CChar', 'CChar16', 'CChar32', 'CDouble', 'CFloat', 'CInt', 'CLong',
            'CLongLong', 'CShort', 'CSignedChar', 'CUnsignedChar', 'CUnsignedInt', 'CUnsignedLong', 'CUnsignedLongLong',
            'CUnsignedShort', 'CWideChar', 'ExtendedGraphemeClusterType', 'Float32', 'Float64', 'FloatLiteralType', 'IntMax',
            'IntegerLiteralType', 'StringLiteralType', 'UIntMax', 'UWord', 'UnicodeScalarType', 'Void', 'Word'
        ),
        /*
        ** Swift Protocols
        */
        6 => array(
            'AbsoluteValuable', 'AnyObject', 'ArrayLiteralConvertible', 'BidirectionalIndexType', 'BitwiseOperationsType',
            'BooleanLiteralConvertible', 'BooleanType', 'CVarArgType', 'CollectionType', 'Comparable', 'DebugPrintable',
            'DictionaryLiteralConvertible', 'Equatable', 'ExtendedGraphemeClusterLiteralConvertible', 'ExtensibleCollectionType',
            'FloatLiteralConvertible', 'FloatingPointType', 'ForwardIndexType', 'GeneratorType', 'Hashable', 'IntegerArithmeticType',
            'IntegerLiteralConvertible', 'IntegerType', 'IntervalType', 'MirrorType', 'MutableCollectionType', 'MutableSliceable',
            'NilLiteralConvertible', 'OutputStreamType', 'Printable', 'RandomAccessIndexType', 'RangeReplaceableCollectionType',
            'RawOptionSetType', 'RawRepresentable', 'Reflectable', 'SequenceType', 'SignedIntegerType', 'SignedNumberType',
            'SinkType', 'Sliceable', 'Streamable', 'Strideable', 'StringInterpolationConvertible', 'StringLiteralConvertible',
            'UnicodeCodecType', 'UnicodeScalarLiteralConvertible', 'UnsignedIntegerType', '_ArrayBufferType', '_ArrayType',
            '_BidirectionalIndexType', '_CVarArgPassedAsDouble', '_CocoaStringType', '_CollectionType', '_Comparable',
            '_DestructorSafeContainer', '_ExtensibleCollectionType', '_ForwardIndexType', '_Incrementable', '_IntegerArithmeticType',
            '_IntegerType', '_NSArrayCoreType', '_NSCopyingType', '_NSDictionaryCoreType', '_NSDictionaryType', '_NSEnumeratorType',
            '_NSFastEnumerationType', '_NSSetCoreType', '_NSSetType', '_NSStringCoreType', '_ObjectiveCBridgeable', '_PointerType',
            '_RandomAccessIndexType', '_RawOptionSetType', '_SequenceType', '_Sequence_Type', '_ShadowProtocol', '_SignedIntegerType',
            '_SignedNumberType', '_Sliceable', '_Strideable', '_StringElementType', '_UnsignedIntegerType', '__ArrayType'
        ),

        /*
        ** Foundation Classes
        */
        7 => array(
            'NSObject', 'NSAffineTransform', 'NSAppleEventDescriptor', 'NSAppleEventManager', 'NSAppleScript', 'NSArray', 'NSMutableArray',
            'NSAssertionHandler', 'NSAttributedString', 'NSMutableAttributedString', 'NSAutoreleasePool', 'NSBackgroundActivityScheduler',
            'NSBundle', 'NSCache', 'NSCachedURLResponse', 'NSCalendar', 'NSCharacterSet', 'NSMutableCharacterSet', 'NSClassDescription',
            'NSScriptClassDescription', 'NSCoder', 'NSArchiver', 'NSKeyedArchiver', 'NSKeyedUnarchiver', 'NSPortCoder', 'NSUnarchiver',
            'NSCondition', 'NSConditionLock', 'NSConnection', 'NSData', 'NSMutableData', 'NSPurgeableData', 'NSDate', 'NSCalendarDate',
            'NSDateComponents', 'NSDecimalNumberHandler', 'NSDictionary', 'NSMutableDictionary', 'NSDistantObjectRequest', 'NSDistributedLock',
            'NSEnumerator', 'NSDirectoryEnumerator', 'NSError', 'NSException', 'NSExpression', 'NSExtensionContext', 'NSExtensionItem',
            'NSFileAccessIntent', 'NSFileCoordinator', 'NSFileHandle', 'NSFileManager', 'NSFileSecurity', 'NSFileVersion', 'NSFileWrapper',
            'NSFormatter', 'NSByteCountFormatter', 'NSDateComponentsFormatter', 'NSDateFormatter', 'NSDateIntervalFormatter',
            'NSEnergyFormatter', 'NSLengthFormatter', 'NSMassFormatter', 'NSNumberFormatter', 'NSGarbageCollector', 'NSHashTable', 'NSHost',
            'NSHTTPCookie', 'NSHTTPCookieStorage', 'NSIndexPath', 'NSIndexSet', 'NSMutableIndexSet', 'NSInvocation', 'NSItemProvider',
            'NSJSONSerialization', 'NSLinguisticTagger', 'NSLocale', 'NSLock', 'NSMapTable', 'NSMetadataItem', 'NSMetadataQuery',
            'NSMetadataQueryAttributeValueTuple', 'NSMetadataQueryResultGroup', 'NSMethodSignature', 'NSNetService', 'NSNetServiceBrowser',
            'NSNotification', 'NSNotificationCenter', 'NSDistributedNotificationCenter', 'NSNotificationQueue', 'NSNull', 'NSOperation',
            'NSBlockOperation', 'NSInvocationOperation', 'NSOperationQueue', 'NSOrderedSet', 'NSMutableOrderedSet', 'NSOrthography', 'NSPipe',
            'NSPointerArray', 'NSPointerFunctions', 'NSPort', 'NSMachPort', 'NSMessagePort', 'NSSocketPort', 'NSPortMessage', 'NSPortNameServer',
            'NSMachBootstrapServer', 'NSMessagePortNameServer', 'NSSocketPortNameServer', 'NSPositionalSpecifier', 'NSPredicate',
            'NSComparisonPredicate', 'NSCompoundPredicate', 'NSProcessInfo', 'NSProgress', 'NSPropertyListSerialization', 'NSRecursiveLock',
            'NSRegularExpression', 'NSDataDetector', 'NSRunLoop', 'NSScanner', 'NSScriptCoercionHandler', 'NSScriptCommand', 'NSCloneCommand',
            'NSCloseCommand', 'NSCountCommand', 'NSCreateCommand', 'NSDeleteCommand', 'NSExistsCommand', 'NSGetCommand', 'NSMoveCommand',
            'NSQuitCommand', 'NSSetCommand', 'NSScriptCommandDescription', 'NSScriptExecutionContext', 'NSScriptObjectSpecifier',
            'NSIndexSpecifier', 'NSMiddleSpecifier', 'NSNameSpecifier', 'NSPropertySpecifier', 'NSRandomSpecifier', 'NSRangeSpecifier',
            'NSRelativeSpecifier', 'NSUniqueIDSpecifier', 'NSWhoseSpecifier', 'NSScriptSuiteRegistry', 'NSScriptWhoseTest', 'NSLogicalTest',
            'NSSpecifierTest', 'NSSet', 'NSMutableSet', 'NSCountedSet', 'NSSortDescriptor', 'NSSpellServer', 'NSStream', 'NSInputStream',
            'NSOutputStream', 'NSString', 'NSMutableString', 'NSTask', 'NSTextCheckingResult', 'NSThread', 'NSTimer', 'NSTimeZone',
            'NSUbiquitousKeyValueStore', 'NSUndoManager', 'NSURL', 'NSURLAuthenticationChallenge', 'NSURLCache', 'NSURLComponents',
            'NSURLConnection', 'NSURLCredential', 'NSURLCredentialStorage', 'NSURLDownload', 'NSURLHandle', 'NSURLProtectionSpace',
            'NSURLProtocol', 'NSURLQueryItem', 'NSURLRequest', 'NSMutableURLRequest', 'NSURLResponse', 'NSHTTPURLResponse', 'NSURLSession',
            'NSURLSessionConfiguration', 'NSURLSessionTask', 'NSURLSessionDataTask', 'NSURLSessionUploadTask', 'NSURLSessionDownloadTask',
            'NSUserActivity', 'NSUserDefaults', 'NSUserNotification', 'NSUserNotificationAction', 'NSUserNotificationCenter',
            'NSUserScriptTask', 'NSUserAppleScriptTask', 'NSUserAutomatorTask', 'NSUserUnixTask', 'NSUUID', 'NSValue', 'NSNumber',
            'NSDecimalNumber', 'NSValueTransformer', 'NSXMLNode', 'NSXMLDocument', 'NSXMLDTD', 'NSXMLDTDNode', 'NSXMLElement', 'NSXMLParser',
            'NSXPCConnection', 'NSXPCInterface', 'NSXPCListener', 'NSXPCListenerEndpoint', 'NSProxy', 'NSDistantObject', 'NSProtocolChecker'
        ),
        /*
        ** Foundation Protocols
        */
        8 => array(
            'NSCacheDelegate', 'NSCoding', 'NSComparisonMethods', 'NSConnectionDelegate', 'NSCopying', 'NSDecimalNumberBehaviors',
            'NSDiscardableContent', 'NSErrorRecoveryAttempting', 'NSExtensionRequestHandling', 'NSFastEnumeration', 'NSFileManagerDelegate',
            'NSFilePresenter', 'NSKeyValueCoding', 'NSKeyValueObserving', 'NSKeyedArchiverDelegate', 'NSKeyedUnarchiverDelegate', 'NSLocking',
            'NSMachPortDelegate', 'NSMetadataQueryDelegate', 'NSMutableCopying', 'NSNetServiceBrowserDelegate', 'NSNetServiceDelegate',
            'NSPortDelegate', 'NSScriptKeyValueCoding', 'NSScriptObjectSpecifiers', 'NSScriptingComparisonMethods',
            'NSSecureCoding', 'NSSpellServerDelegate', 'NSStreamDelegate', 'NSURLAuthenticationChallengeSender',
            'NSURLConnectionDataDelegate', 'NSURLConnectionDelegate', 'NSURLDownloadDelegate', 'NSURLHandleClient', 'NSURLProtocolClient',
            'NSURLSessionDataDelegate', 'NSURLSessionDelegate', 'NSURLSessionDownloadDelegate', 'NSURLSessionTaskDelegate',
            'NSUserActivityDelegate', 'NSUserNotificationCenterDelegate', 'NSXMLParserDelegate', 'NSXPCListenerDelegate',
            'NSXPCProxyCreating'
        ),
        /*
        ** Core Foundation Protocols
        **
        */
        9 => array(
            'CFAllocator', 'CFArray', 'CFAttributedString', 'CFBag', 'CFBinaryHeap', 'CFBitVector', 'CFBoolean', 'CFBundle', 'CFCalendar',
            'CFCharacterSet', 'CFData', 'CFDate', 'CFDateFormatter', 'CFDictionary', 'CFError', 'CFFileDescriptor', 'CFLocale', 'CFMachPort',
            'CFMessagePort', 'CFMutableArray', 'CFMutableAttributedString', 'CFMutableBag', 'CFMutableBitVector', 'CFMutableCharacterSet',
            'CFMutableData', 'CFMutableDictionary', 'CFMutableSet', 'CFMutableString', 'CFNotificationCenter', 'CFNull', 'CFNumber',
            'CFNumberFormatter', 'CFPlugIn', 'CFPlugInInstance', 'CFPropertyList', 'CFReadStream', 'CFRunLoop', 'CFRunLoopObserver',
            'CFRunLoopSource', 'CFRunLoopTimer', 'CFSet', 'CFSocket', 'CFString', 'CFStringTokenizer', 'CFTimeZone', 'CFTree', 'CFType', 'CFURL',
            'CFUUID', 'CFUserNotification', 'CFWriteStream', 'CFXMLNode', 'CFXMLParser', 'CFXMLTree'
        ),

        /*
        ** Core Foundation Data Types
        **
        */
        10 => array(
            'CFAbsoluteTime', 'CFAllocatorContext', 'CFAllocatorRef', 'CFArrayCallBacks', 'CFArrayRef', 'CFAttributedStringRef',
            'CFBagCallBacks', 'CFBagRef', 'CFBinaryHeapCallBacks', 'CFBinaryHeapCompareContext', 'CFBinaryHeapRef', 'CFBit', 'CFBitVectorRef',
            'CFBooleanRef', 'CFBundleRef', 'CFBundleRefNum', 'CFCalendarRef', 'CFCharacterSetPredefinedSet',
            'CFCharacterSetPredefinedSet.AlphaNumeric', 'CFCharacterSetPredefinedSet.CapitalizedLetter',
            'CFCharacterSetPredefinedSet.Control', 'CFCharacterSetPredefinedSet.DecimalDigit',
            'CFCharacterSetPredefinedSet.Decomposable', 'CFCharacterSetPredefinedSet.Illegal', 'CFCharacterSetPredefinedSet.Letter',
            'CFCharacterSetPredefinedSet.LowercaseLetter', 'CFCharacterSetPredefinedSet.Newline', 'CFCharacterSetPredefinedSet.NonBase',
            'CFCharacterSetPredefinedSet.Punctuation', 'CFCharacterSetPredefinedSet.Symbol',
            'CFCharacterSetPredefinedSet.UppercaseLetter', 'CFCharacterSetPredefinedSet.Whitespace',
            'CFCharacterSetPredefinedSet.WhitespaceAndNewline', 'CFCharacterSetRef', 'CFDataRef', 'CFDataSearchFlags', 'CFDateFormatterRef',
            'CFDateFormatterStyle', 'CFDateFormatterStyle.FullStyle', 'CFDateFormatterStyle.LongStyle', 'CFDateFormatterStyle.MediumStyle',
            'CFDateFormatterStyle.NoStyle', 'CFDateFormatterStyle.ShortStyle', 'CFDateRef', 'CFDictionaryKeyCallBacks', 'CFDictionaryRef',
            'CFDictionaryValueCallBacks', 'CFErrorRef', 'CFFileDescriptorCallBack', 'CFFileDescriptorContext',
            'CFFileDescriptorNativeDescriptor', 'CFFileDescriptorRef', 'CFGregorianDate', 'CFGregorianUnits', 'CFHashCode', 'CFIndex',
            'CFLocaleRef', 'CFMachPortContext', 'CFMachPortRef', 'CFMessagePortContext', 'CFMessagePortRef', 'CFMutableArrayRef',
            'CFMutableAttributedStringRef', 'CFMutableBagRef', 'CFMutableBitVectorRef', 'CFMutableCharacterSetRef', 'CFMutableDataRef',
            'CFMutableDictionaryRef', 'CFMutableSetRef', 'CFMutableStringRef', 'CFNotificationCenterRef', 'CFNullRef',
            'CFNumberFormatterOptionFlags', 'CFNumberFormatterPadPosition', 'CFNumberFormatterPadPosition.AfterPrefix',
            'CFNumberFormatterPadPosition.AfterSuffix', 'CFNumberFormatterPadPosition.BeforePrefix',
            'CFNumberFormatterPadPosition.BeforeSuffix', 'CFNumberFormatterRef', 'CFNumberFormatterStyle',
            'CFNumberFormatterStyle.CurrencyStyle', 'CFNumberFormatterStyle.DecimalStyle', 'CFNumberFormatterStyle.NoStyle',
            'CFNumberFormatterStyle.PercentStyle', 'CFNumberFormatterStyle.ScientificStyle', 'CFNumberFormatterStyle.SpellOutStyle',
            'CFNumberRef', 'CFOptionFlags', 'CFPlugInInstanceRef', 'CFPlugInRef', 'CFPropertyListMutabilityOptions', 'CFPropertyListRef',
            'CFRange', 'CFReadStreamRef', 'CFRunLoopObserverContext', 'CFRunLoopObserverRef', 'CFRunLoopRef', 'CFRunLoopSourceContext',
            'CFRunLoopSourceContext1', 'CFRunLoopSourceRef', 'CFRunLoopTimerContext', 'CFRunLoopTimerRef', 'CFSetCallBacks', 'CFSetRef',
            'CFSocketContext', 'CFSocketNativeHandle', 'CFSocketRef', 'CFSocketSignature', 'CFStreamClientContext', 'CFStreamError',
            'CFStringCompareFlags', 'CFStringEncoding', 'CFStringEncodings', 'CFStringEncodings.ANSEL', 'CFStringEncodings.Big5',
            'CFStringEncodings.Big5_E', 'CFStringEncodings.Big5_HKSCS_1999', 'CFStringEncodings.CNS_11643_92_P1',
            'CFStringEncodings.CNS_11643_92_P2', 'CFStringEncodings.CNS_11643_92_P3', 'CFStringEncodings.DOSArabic',
            'CFStringEncodings.DOSBalticRim', 'CFStringEncodings.DOSCanadianFrench', 'CFStringEncodings.DOSChineseSimplif',
            'CFStringEncodings.DOSChineseTrad', 'CFStringEncodings.DOSCyrillic', 'CFStringEncodings.DOSGreek',
            'CFStringEncodings.DOSGreek1', 'CFStringEncodings.DOSGreek2', 'CFStringEncodings.DOSHebrew', 'CFStringEncodings.DOSIcelandic',
            'CFStringEncodings.DOSJapanese', 'CFStringEncodings.DOSKorean', 'CFStringEncodings.DOSLatin1', 'CFStringEncodings.DOSLatin2',
            'CFStringEncodings.DOSLatinUS', 'CFStringEncodings.DOSNordic', 'CFStringEncodings.DOSPortuguese',
            'CFStringEncodings.DOSRussian', 'CFStringEncodings.DOSThai', 'CFStringEncodings.DOSTurkish', 'CFStringEncodings.EBCDIC_CP037',
            'CFStringEncodings.EBCDIC_US', 'CFStringEncodings.EUC_CN', 'CFStringEncodings.EUC_JP', 'CFStringEncodings.EUC_KR',
            'CFStringEncodings.EUC_TW', 'CFStringEncodings.GBK_95', 'CFStringEncodings.GB_18030_2000', 'CFStringEncodings.GB_2312_80',
            'CFStringEncodings.HZ_GB_2312', 'CFStringEncodings.ISOLatin10', 'CFStringEncodings.ISOLatin2', 'CFStringEncodings.ISOLatin3',
            'CFStringEncodings.ISOLatin4', 'CFStringEncodings.ISOLatin5', 'CFStringEncodings.ISOLatin6', 'CFStringEncodings.ISOLatin7',
            'CFStringEncodings.ISOLatin8', 'CFStringEncodings.ISOLatin9', 'CFStringEncodings.ISOLatinArabic',
            'CFStringEncodings.ISOLatinCyrillic', 'CFStringEncodings.ISOLatinGreek', 'CFStringEncodings.ISOLatinHebrew',
            'CFStringEncodings.ISOLatinThai', 'CFStringEncodings.ISO_2022_CN', 'CFStringEncodings.ISO_2022_CN_EXT',
            'CFStringEncodings.ISO_2022_JP', 'CFStringEncodings.ISO_2022_JP_1', 'CFStringEncodings.ISO_2022_JP_2',
            'CFStringEncodings.ISO_2022_JP_3', 'CFStringEncodings.ISO_2022_KR', 'CFStringEncodings.JIS_C6226_78',
            'CFStringEncodings.JIS_X0201_76', 'CFStringEncodings.JIS_X0208_83', 'CFStringEncodings.JIS_X0208_90',
            'CFStringEncodings.JIS_X0212_90', 'CFStringEncodings.KOI8_R', 'CFStringEncodings.KOI8_U', 'CFStringEncodings.KSC_5601_87',
            'CFStringEncodings.KSC_5601_92_Johab', 'CFStringEncodings.MacArabic', 'CFStringEncodings.MacArmenian',
            'CFStringEncodings.MacBengali', 'CFStringEncodings.MacBurmese', 'CFStringEncodings.MacCeltic',
            'CFStringEncodings.MacCentralEurRoman', 'CFStringEncodings.MacChineseSimp', 'CFStringEncodings.MacChineseTrad',
            'CFStringEncodings.MacCroatian', 'CFStringEncodings.MacCyrillic', 'CFStringEncodings.MacDevanagari',
            'CFStringEncodings.MacDingbats', 'CFStringEncodings.MacEthiopic', 'CFStringEncodings.MacExtArabic',
            'CFStringEncodings.MacFarsi', 'CFStringEncodings.MacGaelic', 'CFStringEncodings.MacGeorgian', 'CFStringEncodings.MacGreek',
            'CFStringEncodings.MacGujarati', 'CFStringEncodings.MacGurmukhi', 'CFStringEncodings.MacHFS', 'CFStringEncodings.MacHebrew',
            'CFStringEncodings.MacIcelandic', 'CFStringEncodings.MacInuit', 'CFStringEncodings.MacJapanese',
            'CFStringEncodings.MacKannada', 'CFStringEncodings.MacKhmer', 'CFStringEncodings.MacKorean', 'CFStringEncodings.MacLaotian',
            'CFStringEncodings.MacMalayalam', 'CFStringEncodings.MacMongolian', 'CFStringEncodings.MacOriya',
            'CFStringEncodings.MacRomanLatin1', 'CFStringEncodings.MacRomanian', 'CFStringEncodings.MacSinhalese',
            'CFStringEncodings.MacSymbol', 'CFStringEncodings.MacTamil', 'CFStringEncodings.MacTelugu', 'CFStringEncodings.MacThai',
            'CFStringEncodings.MacTibetan', 'CFStringEncodings.MacTurkish', 'CFStringEncodings.MacUkrainian', 'CFStringEncodings.MacVT100',
            'CFStringEncodings.MacVietnamese', 'CFStringEncodings.NextStepJapanese', 'CFStringEncodings.ShiftJIS',
            'CFStringEncodings.ShiftJIS_X0213', 'CFStringEncodings.ShiftJIS_X0213_MenKuTen', 'CFStringEncodings.UTF7',
            'CFStringEncodings.UTF7_IMAP', 'CFStringEncodings.VISCII', 'CFStringEncodings.WindowsArabic',
            'CFStringEncodings.WindowsBalticRim', 'CFStringEncodings.WindowsCyrillic', 'CFStringEncodings.WindowsGreek',
            'CFStringEncodings.WindowsHebrew', 'CFStringEncodings.WindowsKoreanJohab', 'CFStringEncodings.WindowsLatin2',
            'CFStringEncodings.WindowsLatin5', 'CFStringEncodings.WindowsVietnamese', 'CFStringInlineBuffer', 'CFStringRef',
            'CFStringTokenizerRef', 'CFSwappedFloat32', 'CFSwappedFloat64', 'CFTimeInterval', 'CFTimeZoneNameStyle',
            'CFTimeZoneNameStyle.DaylightSaving', 'CFTimeZoneNameStyle.Generic', 'CFTimeZoneNameStyle.ShortDaylightSaving',
            'CFTimeZoneNameStyle.ShortGeneric', 'CFTimeZoneNameStyle.ShortStandard', 'CFTimeZoneNameStyle.Standard', 'CFTimeZoneRef',
            'CFTreeContext', 'CFTreeRef', 'CFTypeID', 'CFTypeRef', 'CFURLBookmarkCreationOptions', 'CFURLBookmarkFileCreationOptions',
            'CFURLBookmarkResolutionOptions', 'CFURLRef', 'CFUUIDBytes', 'CFUUIDRef', 'CFUserNotificationRef', 'CFWriteStreamRef',
            'CFXMLAttributeDeclarationInfo', 'CFXMLAttributeListDeclarationInfo', 'CFXMLDocumentInfo', 'CFXMLDocumentTypeInfo',
            'CFXMLElementInfo', 'CFXMLElementTypeDeclarationInfo', 'CFXMLEntityInfo', 'CFXMLEntityReferenceInfo', 'CFXMLExternalID',
            'CFXMLNodeRef', 'CFXMLNotationInfo', 'CFXMLParserCallBacks', 'CFXMLParserContext', 'CFXMLParserRef',
            'CFXMLProcessingInstructionInfo', 'CFXMLTreeRef'
        ),
        /*
        ** Core Graphics Protocols
        **
        */
        11 => array(
            'CGBitmapContext', 'CGColor', 'CGColorSpace', 'CGContext', 'CGDataConsumer', 'CGDataProvider', 'CGFont', 'CGFunction', 'CGGradient',
            'CGImage', 'CGLayer', 'CGPath', 'CGPattern', 'CGPDFArray', 'CGPDFContentStream', 'CGPDFContext', 'CGPDFDictionary', 'CGPDFDocument',
            'CGPDFObject', 'CGPDFOperatorTable', 'CGPDFPage', 'CGPDFScanner', 'CGPDFStream', 'CGPDFString', 'CGShading'
        ),

        /*
        ** Core Graphics Data Types
        **
        */
        12 => array(
            'CGBitmapContextReleaseDataCallback', 'CGColorRef', 'CGColorSpaceRef', 'CGContextRef', 'CGDataConsumerCallbacks',
            'CGDataConsumerRef', 'CGDataProviderRef', 'CGDataProviderDirectCallbacks', 'CGDataProviderSequentialCallbacks', 'CGFontRef',
            'CGFontIndex', 'CGGlyph', 'CGFunctionRef', 'CGFunctionCallbacks', 'CGGradientRef', 'CGImageRef', 'CGLayerRef', 'CGPathRef',
            'CGMutablePathRef', 'CGPathElement', 'CGPatternRef', 'CGPatternCallbacks', 'CGPDFArrayRef', 'CGPDFContentStreamRef',
            'CGPDFDictionaryRef', 'CGPDFDocumentRef', 'CGPDFObjectRef', 'CGPDFBoolean', 'CGPDFInteger', 'CGPDFReal', 'CGPDFOperatorTableRef',
            'CGPDFPageRef', 'CGPDFScannerRef', 'CGPDFStreamRef', 'CGPDFStringRef', 'CGShadingRef', 'CGAffineTransform', 'CGFloat', 'CGPoint', 'CGRect',
            'CGSize', 'CGVector', 'CGError'
        ),
        /*
        ** UIKit Classes
        **
        */
        13 => array(
            'NSFileProviderExtension', 'NSLayoutConstraint', 'NSLayoutManager', 'NSParagraphStyle', 'NSMutableParagraphStyle',
            'NSShadow', 'NSStringDrawingContext', 'NSTextAttachment', 'NSTextContainer', 'NSTextTab', 'UIAcceleration', 'UIAccelerometer',
            'UIAccessibilityCustomAction', 'UIAccessibilityElement', 'UIActivity', 'UIAlertAction', 'UIBarItem', 'UIBarButtonItem',
            'UITabBarItem', 'UIBezierPath', 'UICollectionViewLayout', 'UICollectionViewFlowLayout', 'UICollectionViewTransitionLayout',
            'UICollectionViewLayoutAttributes', 'UICollectionViewLayoutInvalidationContext',
            'UICollectionViewFlowLayoutInvalidationContext', 'UICollectionViewUpdateItem', 'UIColor', 'UIDevice', 'UIDictationPhrase',
            'UIDocument', 'UIManagedDocument', 'UIDocumentInteractionController', 'UIDynamicAnimator', 'UIDynamicBehavior',
            'UIAttachmentBehavior', 'UICollisionBehavior', 'UIDynamicItemBehavior', 'UIGravityBehavior', 'UIPushBehavior', 'UISnapBehavior',
            'UIEvent', 'UIFont', 'UIFontDescriptor', 'UIGestureRecognizer', 'UILongPressGestureRecognizer', 'UIPanGestureRecognizer',
            'UIScreenEdgePanGestureRecognizer', 'UIPinchGestureRecognizer', 'UIRotationGestureRecognizer', 'UISwipeGestureRecognizer',
            'UITapGestureRecognizer', 'UIImage', 'UIImageAsset', 'UIKeyCommand', 'UILexicon', 'UILexiconEntry', 'UILocalNotification',
            'UILocalizedIndexedCollation', 'UIMenuController', 'UIMenuItem', 'UIMotionEffect', 'UIInterpolatingMotionEffect',
            'UIMotionEffectGroup', 'UINavigationItem', 'UINib', 'UIPasteboard', 'UIPercentDrivenInteractiveTransition', 'UIPopoverController',
            'UIPresentationController', 'UIPopoverPresentationController', 'UIPrintFormatter', 'UIMarkupTextPrintFormatter',
            'UISimpleTextPrintFormatter', 'UIViewPrintFormatter', 'UIPrintInfo', 'UIPrintInteractionController', 'UIPrintPageRenderer',
            'UIPrintPaper', 'UIPrinter', 'UIPrinterPickerController', 'UIResponder', 'UIApplication', 'UIView', 'UIActionSheet',
            'UIActivityIndicatorView', 'UIAlertView', 'UICollectionReusableView', 'UICollectionViewCell', 'UIControl', 'UIButton',
            'UIDatePicker', 'UIPageControl', 'UIRefreshControl', 'UISegmentedControl', 'UISlider', 'UIStepper', 'UISwitch', 'UITextField',
            'UIImageView', 'UIInputView', 'UILabel', 'UINavigationBar', 'UIPickerView', 'UIPopoverBackgroundView', 'UIProgressView',
            'UIScrollView', 'UICollectionView', 'UITableView', 'UITextView', 'UISearchBar', 'UITabBar', 'UITableViewCell',
            'UITableViewHeaderFooterView', 'UIToolbar', 'UIVisualEffectView', 'UIWebView', 'UIWindow', 'UIViewController',
            'UIActivityViewController', 'UIAlertController', 'UICollectionViewController', 'UIDocumentMenuViewController',
            'UIDocumentPickerExtensionViewController', 'UIDocumentPickerViewController', 'UIInputViewController', 'UINavigationController',
            'UIImagePickerController', 'UIVideoEditorController', 'UIPageViewController', 'UIReferenceLibraryViewController',
            'UISearchController', 'UISplitViewController', 'UITabBarController', 'UITableViewController', 'UIScreen', 'UIScreenMode',
            'UISearchDisplayController', 'UIStoryboard', 'UIStoryboardSegue', 'UIStoryboardPopoverSegue', 'UITableViewRowAction',
            'UITextChecker', 'UITextInputMode', 'UITextInputStringTokenizer', 'UITextPosition', 'UITextRange', 'UITextSelectionRect', 'UITouch',
            'UITraitCollection', 'UIUserNotificationAction', 'UIMutableUserNotificationAction', 'UIUserNotificationCategory',
            'UIMutableUserNotificationCategory', 'UIUserNotificationSettings', 'UIVisualEffect', 'UIBlurEffect', 'UIVibrancyEffect',
            'NSTextStorage', 'UIActivityItemProvider'
        ),
        /*
        ** UIKit Protocols
        **
        */
        14 => array(
            'NSLayoutManagerDelegate', 'NSTextAttachmentContainer', 'NSTextLayoutOrientationProvider', 'NSTextStorageDelegate',
            'UIAccelerometerDelegate', 'UIAccessibility', 'UIAccessibilityAction', 'UIAccessibilityContainer', 'UIAccessibilityFocus',
            'UIAccessibilityIdentification', 'UIAccessibilityReadingContent', 'UIActionSheetDelegate', 'UIActivityItemSource',
            'UIAdaptivePresentationControllerDelegate', 'UIAlertViewDelegate', 'UIAppearance', 'UIAppearanceContainer',
            'UIApplicationDelegate', 'UIBarPositioning', 'UIBarPositioningDelegate', 'UICollectionViewDataSource',
            'UICollectionViewDelegate', 'UICollectionViewDelegateFlowLayout', 'UICollisionBehaviorDelegate', 'UIContentContainer',
            'UICoordinateSpace', 'UIDataSourceModelAssociation', 'UIDocumentInteractionControllerDelegate', 'UIDocumentMenuDelegate',
            'UIDocumentPickerDelegate', 'UIDynamicAnimatorDelegate', 'UIDynamicItem', 'UIGestureRecognizerDelegate',
            'UIGuidedAccessRestrictionDelegate', 'UIImagePickerControllerDelegate', 'UIInputViewAudioFeedback', 'UIKeyInput',
            'UILayoutSupport', 'UINavigationBarDelegate', 'UINavigationControllerDelegate', 'UIObjectRestoration',
            'UIPageViewControllerDataSource', 'UIPageViewControllerDelegate', 'UIPickerViewAccessibilityDelegate',
            'UIPickerViewDataSource', 'UIPickerViewDelegate', 'UIPopoverBackgroundViewMethods', 'UIPopoverControllerDelegate',
            'UIPopoverPresentationControllerDelegate', 'UIPrintInteractionControllerDelegate', 'UIPrinterPickerControllerDelegate',
            'UIResponderStandardEditActions', 'UIScrollViewAccessibilityDelegate', 'UIScrollViewDelegate', 'UISearchBarDelegate',
            'UISearchControllerDelegate', 'UISearchDisplayDelegate', 'UISearchResultsUpdating', 'UISplitViewControllerDelegate',
            'UIStateRestoring', 'UITabBarControllerDelegate', 'UITabBarDelegate', 'UITableViewDataSource', 'UITableViewDelegate',
            'UITextDocumentProxy', 'UITextFieldDelegate', 'UITextInput', 'UITextInputDelegate', 'UITextInputTokenizer', 'UITextInputTraits',
            'UITextViewDelegate', 'UIToolbarDelegate', 'UITraitEnvironment', 'UIVideoEditorControllerDelegate',
            'UIViewControllerAnimatedTransitioning', 'UIViewControllerContextTransitioning', 'UIViewControllerInteractiveTransitioning',
            'UIViewControllerRestoration', 'UIViewControllerTransitionCoordinator', 'UIViewControllerTransitionCoordinatorContext',
            'UIViewControllerTransitioningDelegate', 'UIWebViewDelegate'
        ),
        /*
        ** AppKit Classes
        **
        */
        15 => array(
            'NSAccessibilityElement', 'NSAlert', 'NSAnimation', 'NSViewAnimation', 'NSAnimationContext', 'NSAppearance',
            'NSBezierPath', 'NSCell', 'NSActionCell', 'NSButtonCell', 'NSMenuItemCell', 'NSPopUpButtonCell', 'NSDatePickerCell', 'NSFormCell',
            'NSLevelIndicatorCell', 'NSPathCell', 'NSSegmentedCell', 'NSSliderCell', 'NSStepperCell', 'NSTextFieldCell', 'NSComboBoxCell',
            'NSPathComponentCell', 'NSSearchFieldCell', 'NSSecureTextFieldCell', 'NSTableHeaderCell', 'NSTokenFieldCell', 'NSBrowserCell',
            'NSImageCell', 'NSTextAttachmentCell', 'NSColor', 'NSColorList', 'NSColorPicker', 'NSColorSpace', 'NSController', 'NSObjectController',
            'NSArrayController', 'NSDictionaryController', 'NSTreeController', 'NSUserDefaultsController', 'NSCursor', 'NSDockTile',
            'NSDocument', 'NSPersistentDocument', 'NSDocumentController', 'NSDraggingImageComponent', 'NSDraggingItem', 'NSDraggingSession',
            'NSEvent', 'NSFont', 'NSFontCollection', 'NSMutableFontCollection', 'NSFontDescriptor', 'NSFontManager', 'NSGestureRecognizer',
            'NSClickGestureRecognizer', 'NSMagnificationGestureRecognizer', 'NSPanGestureRecognizer', 'NSPressGestureRecognizer',
            'NSRotationGestureRecognizer', 'NSGlyphGenerator', 'NSGlyphInfo', 'NSGradient', 'NSGraphicsContext', 'NSHelpManager', 'NSImage',
            'NSImageRep', 'NSBitmapImageRep', 'NSCachedImageRep', 'NSCIImageRep', 'NSCustomImageRep', 'NSEPSImageRep', 'NSPDFImageRep',
            'NSPICTImageRep', 'NSInputManager', 'NSInputServer', 'NSMediaLibraryBrowserController',
            'NSMenu', 'NSMenuItem', 'NSMovie', 'NSNib', 'NSNibConnector', 'NSNibControlConnector', 'NSNibOutletConnector', 'NSOpenGLContext',
            'NSOpenGLPixelBuffer', 'NSOpenGLPixelFormat', 'NSPageLayout', 'NSPasteboard',
            'NSPasteboardItem', 'NSPathControlItem', 'NSPDFInfo', 'NSPDFPanel', 'NSPredicateEditorRowTemplate', 'NSPrinter', 'NSPrintInfo',
            'NSPrintOperation', 'NSPrintPanel', 'NSResponder', 'NSApplication', 'NSDrawer', 'NSPopover', 'NSView', 'NSBox', 'NSClipView',
            'NSCollectionView', 'NSControl', 'NSBrowser', 'NSButton', 'NSPopUpButton', 'NSStatusBarButton', 'NSColorWell', 'NSDatePicker',
            'NSImageView', 'NSLevelIndicator', 'NSMatrix', 'NSForm', 'NSPathControl', 'NSRuleEditor', 'NSPredicateEditor', 'NSScroller',
            'NSSegmentedControl', 'NSSlider', 'NSStepper', 'NSTableView', 'NSOutlineView', 'NSTextField', 'NSComboBox', 'NSSearchField',
            'NSSecureTextField', 'NSTokenField', 'NSMenuView', 'NSMovieView', 'NSOpenGLView', 'NSProgressIndicator', 'NSQuickDrawView',
            'NSRulerView', 'NSScrollView', 'NSSplitView', 'NSStackView', 'NSTableCellView', 'NSTableHeaderView', 'NSTableRowView', 'NSTabView',
            'NSText', 'NSTextView', 'NSVisualEffectView', 'NSViewController', 'NSCollectionViewItem', 'NSPageController',
            'NSSplitViewController', 'NSTabViewController', 'NSTitlebarAccessoryViewController', 'NSWindow', 'NSPanel', 'NSColorPanel',
            'NSFontPanel', 'NSSavePanel', 'NSOpenPanel', 'NSWindowController', 'NSRulerMarker', 'NSRunningApplication', 'NSScreen',
            'NSSharingService', 'NSSharingServicePicker', 'NSSound', 'NSSpeechRecognizer', 'NSSpeechSynthesizer', 'NSSpellChecker',
            'NSSplitViewItem', 'NSStatusBar', 'NSStatusItem', 'NSStoryboard', 'NSStoryboardSegue', 'NSTableColumn', 'NSTabViewItem',
            'NSTextAlternatives', 'NSTextBlock', 'NSTextTable', 'NSTextTableBlock', 'NSTextFinder',
            'NSTextInputContext', 'NSTextList', 'NSToolbar', 'NSToolbarItem', 'NSToolbarItemGroup', 'NSTouch', 'NSTrackingArea',
            'NSTreeNode', 'NSTypesetter', 'NSATSTypesetter', 'NSWorkspace', 'CAOpenGLLayer',
            'NSOpenGLLayer'
        ),
        /*
        ** AppKit Protocols
        **
        */
        16 => array(
            'NSAccessibility', 'NSAccessibility Informal', 'NSAccessibilityButton', 'NSAccessibilityCheckBox',
            'NSAccessibilityContainsTransientUI', 'NSAccessibilityGroup', 'NSAccessibilityImage',
            'NSAccessibilityLayoutArea', 'NSAccessibilityLayoutItem', 'NSAccessibilityList', 'NSAccessibilityNavigableStaticText',
            'NSAccessibilityOutline', 'NSAccessibilityProgressIndicator', 'NSAccessibilityRadioButton', 'NSAccessibilityRow',
            'NSAccessibilitySlider', 'NSAccessibilityStaticText', 'NSAccessibilityStepper', 'NSAccessibilitySwitch', 'NSAccessibilityTable',
            'NSAlertDelegate', 'NSAnimatablePropertyContainer', 'NSAnimationDelegate', 'NSAppearanceCustomization', 'NSApplicationDelegate',
            'NSBrowserDelegate', 'NSChangeSpelling', 'NSCollectionViewDelegate', 'NSColorPickingCustom', 'NSColorPickingDefault',
            'NSComboBoxCellDataSource', 'NSComboBoxDataSource', 'NSComboBoxDelegate', 'NSControlTextEditingDelegate',
            'NSDatePickerCellDelegate', 'NSDictionaryControllerKeyValuePair', 'NSDraggingDestination', 'NSDraggingInfo', 'NSDraggingSource',
            'NSDrawerDelegate', 'NSEditor', 'NSEditorRegistration', 'NSFontPanelValidation', 'NSGestureRecognizerDelegate', 'NSGlyphStorage',
            'NSIgnoreMisspelledWords', 'NSImageDelegate', 'NSKeyValueBindingCreation', 'NSLayerDelegateContentsScaleUpdating',
            'NSMatrixDelegate', 'NSMenuDelegate', 'NSMenuValidation', 'NSNibAwaking', 'NSOpenSavePanelDelegate',
            'NSOutlineViewDataSource', 'NSOutlineViewDelegate', 'NSPageControllerDelegate', 'NSPasteboardItemDataProvider',
            'NSPasteboardReading', 'NSPasteboardWriting', 'NSPathCellDelegate', 'NSPathControlDelegate', 'NSPlaceholders', 'NSPopoverDelegate',
            'NSPrintPanelAccessorizing', 'NSRuleEditorDelegate', 'NSSeguePerforming', 'NSServicesMenuRequestor', 'NSSharingServiceDelegate',
            'NSSharingServicePickerDelegate', 'NSSoundDelegate', 'NSSpeechRecognizerDelegate', 'NSSpeechSynthesizerDelegate',
            'NSSplitViewDelegate', 'NSStackViewDelegate', 'NSTabViewDelegate', 'NSTableViewDataSource', 'NSTableViewDelegate',
            'NSTextDelegate', 'NSTextFieldDelegate', 'NSTextFinderBarContainer', 'NSTextFinderClient', 'NSTextInput',
            'NSTextInputClient', 'NSTextViewDelegate',
            'NSTokenFieldCellDelegate', 'NSTokenFieldDelegate', 'NSToolTipOwner', 'NSToolbarDelegate', 'NSToolbarItemValidation',
            'NSUserInterfaceItemIdentification', 'NSUserInterfaceItemSearchDataSource', 'NSUserInterfaceItemSearching',
            'NSUserInterfaceValidations', 'NSValidatedUserInterfaceItem', 'NSViewControllerPresentationAnimator', 'NSWindowDelegate',
            'NSWindowRestoration', 'NSWindowScripting'
        )
    ),
    'SYMBOLS' => array(
        # Operators
        1 => array(
            '!=', '!==', '%', '%=', '&', '&&', '&*', '&+', '&-', '&=', '*', '*=', '+', '++', '+=', '-', '--', '-=', '...', '..<', '/',
            '/=', '<', '<<', '<<=', '<=', '==', '===', '>', '>=', '>>', '>>=', '??', '^', '^=', '|', '|=', '||', '~=', '~>', '!', '~'
        ),
        # Structure
        2 => array(
            '(', ')', '[', ']', '{', '}', ',', ';', ':'
        )
    ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => true,
        2 => true,
        3 => true,
        4 => true,
        5 => true,
        6 => true,
        7 => true,
        8 => true,
        9 => true,
        10 => true,
        11 => true,
        12 => true,
        13 => true,
        14 => true,
        15 => true,
        16 => true,
    ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #B833A1;',                        // Keywords
            2 => 'color: #B833A1;',                        // Attributes
            3 => 'color: #508187;',                        // Builtin Functions
            4 => 'color: #6F41A7;',                        // Types
            5 => 'color: #6F41A7;',
            6 => 'color: #6F41A7;',
            7 => 'color: #6F41A7;',
            8 => 'color: #6F41A7;',
            9 => 'color: #6F41A7;',
            10 => 'color: #6F41A7;',
            11 => 'color: #6F41A7;',
            12 => 'color: #6F41A7;',
            13 => 'color: #6F41A7;',
            14 => 'color: #6F41A7;',
            15 => 'color: #6F41A7;',
            16 => 'color: #6F41A7;'
        ),
        'COMMENTS' => array(
            1 => 'color: #008312;',
            'MULTI' => 'color: #008312;'
        ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #C41A16;'
        ),
        'BRACKETS' => array(
            0 => 'color: black;'
        ),
        'STRINGS' => array(
            0 => 'color: #C41A16;'
        ),
        'NUMBERS' => array(
            0 => 'color: #1C00CF;',
            1 => 'color: #1C00CF;',
            2 => 'color: #1C00CF;',
            3 => 'color: #1C00CF;',
        ),
        'METHODS' => array(
            1 => 'color: #508187;'
        ),
        'SYMBOLS' => array(
            0 => 'color: black;'
        ),
        'REGEXPS' => array(),
        'SCRIPT' => array()
    ),
    'URLS' => array(
        1 => '',
        2 => '',
        3 => '',
        4 => '',
        5 => '',
        6 => '',
        7 => '',
        8 => '',
        9 => '',
        10 => '',
        11 => '',
        12 => '',
        13 => '',
        14 => '',
        15 => '',
        16 => '',
    ),
    'OOLANG' => true,
    'OBJECT_SPLITTERS' => array(
        1 => '.'
    ),
    'REGEXPS' => array(),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(),
    'HIGHLIGHT_STRICT_BLOCK' => array()
);
