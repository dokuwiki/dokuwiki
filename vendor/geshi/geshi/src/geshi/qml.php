<?php
/*************************************************************************************
 * qml.php
 * --------------
 * Author: J-P Nurmi <jpnurmi@gmail.com>
 * Copyright: (c) 2012-2014 J-P Nurmi <jpnurmi@gmail.com>
 * Release Version: 1.0.9.1
 * Date Started: 2012/08/19
 *
 * QML language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2014/06/29 (1.0.8.13)
 *  - Synced QML types from Qt 5.3:
 *    http://qt-project.org/doc/qt-5/modules-qml.html
 * 2012/08/19
 *  - First version based on Qt 4
 *
 * TODO (updated 2014/06/29)
 * -------------------------
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
    'LANG_NAME' => 'QML',
    'COMMENT_SINGLE' => array(1 => '//'),
    'COMMENT_MULTI' => array('/*' => '*/'),
    'COMMENT_REGEXP' => array(
        // comments
        2 => "/(?<=[\\s^])(s|tr|y)\\/(?!\*)(?!\s)(?:\\\\.|(?!\n)[^\\/\\\\])+(?<!\s)\\/(?!\s)(?:\\\\.|(?!\n)[^\\/\\\\])*(?<!\s)\\/[msixpogcde]*(?=[\\s$\\.\\;])|(?<=[\\s^(=])(m|q[qrwx]?)?\\/(?!\*)(?!\s)(?:\\\\.|(?!\n)[^\\/\\\\])+(?<!\s)\\/[msixpogc]*(?=[\\s$\\.\\,\\;\\)])/iU",
        // property binding
        3 => "/([a-z][\\w\\.]*)(?=:)/",
        // TODO: property name (fixed length lookbehind assertion?)
        4 => "/(?<=property\\s+\\w+\\s+)(\\w+)/"
        ),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array("'", '"'),
    'ESCAPE_CHAR' => '\\',
    'KEYWORDS' => array(
        1 => array(
            'as','alias','break','case','catch','continue','const','debugger',
            'default','delete','do','else','finally','for','function',
            'if','import','in','instanceof','new','on','public','property',
            'readonly','return','signal','switch','this','throw','try',
            'typeof','while','with'
            ),
        2 => array(
            'action','bool','color','date','double','enumeration','font',
            'int','list','matrix4x4','point','quaternion','real','rect',
            'size','string','time','url','var','variant','vector2d',
            'vector3d','vector4d','void'
            ),
        // http://qt-project.org/doc/qt-5/qtbluetooth-qmlmodule.html
        3 => array(
            'BluetoothDiscoveryModel','BluetoothService','BluetoothSocket'
            ),
        // http://qt-project.org/doc/qt-5/qtgraphicaleffects-qmlmodule.html
        4 => array(
            'Blend','BrightnessContrast','ColorOverlay','Colorize',
            'ConicalGradient','Desaturate','DirectionalBlur','Displace',
            'DropShadow','FastBlur','GammaAdjust','GaussianBlur','Glow',
            'HueSaturation','InnerShadow','LevelAdjust','LinearGradient',
            'MaskedBlur','OpacityMask','RadialBlur','RadialGradient',
            'RectangularGlow','RecursiveBlur','ThresholdMask','ZoomBlur'
            ),
        // http://qt-project.org/doc/qt-5/qtaudioengine-qmlmodule.html
        5 => array(
            'AttenuationModelLinear','AttenuationModelInverse','AudioCategory',
            'AudioEngine','AudioListener','AudioSample','PlayVariation',
            'Sound','SoundInstance'
            ),
        // http://qt-project.org/doc/qt-5/qtmultimedia-qmlmodule.html
        6 => array(
            'Video','Audio','MediaPlayer','Camera','CameraCapture',
            'CameraExposure','CameraFlash','CameraFocus','CameraImageProcessing',
            'CameraRecorder','Radio','RadioData','Torch','SoundEffect','VideoOutput'
            ),
        // http://qt-project.org/doc/qt-5/qtnfc-qmlmodule.html
        7 => array(
            'NdefFilter','NdefMimeRecord','NdefTextRecord','NdefUriRecord',
            'NearField','NdefRecord'
            ),
        // http://qt-project.org/doc/qt-5/qtpositioning-qmlmodule.html
        8 => array(
            'QtPositioning','CoordinateAnimation','Position','PositionSource',
            'Address','Location'
            ),
        // http://qt-project.org/doc/qt-5/qtqml-models-qmlmodule.html
        9 => array(
            'DelegateModel','DelegateModelGroup','ListModel','ListElement','ObjectModel'
            ),
        // http://qt-project.org/doc/qt-5/qtqml-qmlmodule.html
        10 => array(
            'Binding','Component','Connections','Date','Instantiator',
            'Locale','Number','Qt','QtObject','String','Timer'
            ),
        // http://qt-project.org/doc/qt-5/qt-labs-folderlistmodel-qmlmodule.html
        11 => array(
            'FolderListModel'
            ),
        // http://qt-project.org/doc/qt-5/qtquick-localstorage-qmlmodule.html
        12 => array(
            'openDatabaseSync'
            ),
        // http://qt-project.org/doc/qt-5/qt-labs-settings-qmlmodule.html
        13 => array(
            'Settings'
            ),
        // http://qt-project.org/doc/qt-5/qtquick-window-qmlmodule.html
        14 => array(
            'Screen','Window','CloseEvent'
            ),
        // http://qt-project.org/doc/qt-5/qtquick-xmllistmodel-qmlmodule.html
        15 => array(
            'XmlRole','XmlListModel'
            ),
        // http://qt-project.org/doc/qt-5/qtquick-particles-qmlmodule.html
        16 => array(
            'Age','AngleDirection','CumulativeDirection','CustomParticle',
            'Direction','EllipseShape','Friction','Gravity','GroupGoal',
            'ImageParticle','ItemParticle','LineShape','MaskShape','Affector',
            'Emitter','Shape','ParticleGroup','ParticlePainter','ParticleSystem',
            'Attractor','PointDirection','RectangleShape','SpriteGoal',
            'TargetDirection','TrailEmitter','Turbulence','Particle','Wander'
            ),
        // http://qt-project.org/doc/qt-5/qttest-qmlmodule.html
        17 => array(
            'SignalSpy','TestCase'
            ),
        // http://qt-project.org/doc/qt-5/qtquick-qmltypereference.html
        18 => array(
            'Item','Rectangle','Image','BorderImage','AnimatedImage','AnimatedSprite',
            'SpriteSequence','Text','Accessible','Gradient','GradientStop','SystemPalette',
            'Sprite','FontLoader','Repeater','Loader','Visual Item Transformations','Transform',
            'Scale','Rotation','Translate','MouseArea','Keys','KeyNavigation','FocusScope',
            'Flickable','PinchArea','MultiPointTouchArea','Drag','DropArea','TextInput',
            'TextEdit','IntValidator','DoubleValidator','RegExpValidator','TouchPoint',
            'PinchEvent','WheelEvent','MouseEvent','KeyEvent','DragEvent','Positioner',
            'Column','Row','Grid','Flow','LayoutMirroring','State','PropertyChanges',
            'StateGroup','StateChangeScript','ParentChange','AnchorChanges','Transition',
            'ViewTransition','SequentialAnimation','ParallelAnimation','Behavior','PropertyAction',
            'PauseAnimation','SmoothedAnimation','SpringAnimation','ScriptAction','PropertyAnimation',
            'NumberAnimation','Vector3dAnimation','ColorAnimation','RotationAnimation','ParentAnimation',
            'AnchorAnimation','PathAnimation','XAnimator','YAnimator','ScaleAnimator','RotationAnimator',
            'OpacityAnimator','UniformAnimator','Lower-level Animation Types','PathInterpolator',
            'AnimationController','Path','PathLine','PathQuad','PathCubic','PathArc','PathCurve',
            'PathSvg','PathAttribute','PathPercent','VisualItemModel','VisualDataModel','VisualDataGroup',
            'ListView','GridView','PathView','Package','Flipable','ShaderEffect','ShaderEffectSource',
            'GridMesh','WorkerScript','Canvas','Context2D','CanvasGradient','CanvasPixelArray',
            'CanvasImageData','TextMetrics',
            ),
        // http://qt-project.org/doc/qt-5/qtquick-controls-qmlmodule.html
        19 => array(
            'ApplicationWindow','BusyIndicator','Button','Calendar',
            'CheckBox','ComboBox','GroupBox','Label','Menu','MenuBar',
            'ProgressBar','RadioButton','ScrollView','Slider','SpinBox',
            'SplitView','StackView','StackViewDelegate','StatusBar',
            'Switch','Tab','TabView','TableView','TableViewColumn',
            'TextArea','TextField','ToolBar','ToolButton','Action',
            'ExclusiveGroup','MenuSeparator','MenuItem','Stack'
            ),
        // http://qt-project.org/doc/qt-5/qtquick-dialogs-qmlmodule.html
        20 => array(
            'Dialog','ColorDialog','FileDialog','FontDialog','MessageDialog'
            ),
        // http://qt-project.org/doc/qt-5/qtquick-layouts-qmlmodule.html
        21 => array(
            'Layout','RowLayout','ColumnLayout','GridLayout'
            ),
        // http://qt-project.org/doc/qt-5/qtsensors-qmlmodule.html
        22 => array(
            'Accelerometer','AccelerometerReading','Altimeter','AltimeterReading',
            'AmbientLightReading','AmbientLightSensor','AmbientTemperatureReading',
            'AmbientTemperatureSensor','Compass','CompassReading','Gyroscope',
            'GyroscopeReading','HolsterReading','HolsterSensor','IRProximityReading',
            'IRProximitySensor','LightReading','LightSensor','Magnetometer',
            'MagnetometerReading','OrientationReading','OrientationSensor',
            'PressureReading','PressureSensor','ProximityReading','ProximitySensor',
            'RotationReading','RotationSensor','SensorGesture','SensorGlobal',
            'SensorReading','TapReading','TapSensor','TiltReading','TiltSensor'
            ),
        // http://qt-project.org/doc/qt-5/qtwinextras-qmlmodule.html
        23 => array(
            'JumpListDestination','JumpListLink','JumpListSeparator','DwmFeatures',
            'JumpList','JumpListCategory','TaskbarButton','ThumbnailToolBar','ThumbnailToolButton'
            ),
        // http://qt-project.org/doc/qt-5/qtwebkit-qmlmodule.html
        24 => array(
            'WebView','WebLoadRequest'
            )
        ),
    'SYMBOLS' => array(
        '(', ')', '[', ']', '{', '}',
        '+', '-', '*', '/', '%',
        '!', '@', '&', '|', '^',
        '<', '>', '=',
        ',', ';', '?', ':'
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
        17 => true,
        18 => true,
        19 => true,
        20 => true,
        21 => true,
        22 => true,
        23 => true,
        24 => true
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #808041;',
            2 => 'color: #808041;',
            3 => 'color: #800780;',
            4 => 'color: #800780;',
            5 => 'color: #800780;',
            6 => 'color: #800780;',
            7 => 'color: #800780;',
            8 => 'color: #800780;',
            9 => 'color: #800780;',
            10 => 'color: #800780;',
            11 => 'color: #800780;',
            12 => 'color: #800780;',
            13 => 'color: #800780;',
            14 => 'color: #800780;',
            15 => 'color: #800780;',
            16 => 'color: #800780;',
            17 => 'color: #800780;',
            18 => 'color: #800780;',
            19 => 'color: #800780;',
            20 => 'color: #800780;',
            21 => 'color: #800780;',
            22 => 'color: #800780;',
            23 => 'color: #800780;',
            24 => 'color: #800780;'
            ),
        'COMMENTS' => array(
            1 => 'color: #008025;',
            2 => 'color: #008025;',
            3 => 'color: #970009;',
            4 => 'color: #970009;',
            'MULTI' => 'color: #008025;'
            ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #000099; font-weight: bold;'
            ),
        'BRACKETS' => array(
            0 => 'color: #000000;'
            ),
        'STRINGS' => array(
            0 => 'color: #008025;'
            ),
        'NUMBERS' => array(
            0 => 'color: #000000;'
            ),
        'METHODS' => array(
            1 => 'color: #000000;'
            ),
        'SYMBOLS' => array(
            0 => 'color: #000000;'
            ),
        'REGEXPS' => array(
            ),
        'SCRIPT' => array(
            0 => '',
            1 => '',
            2 => '',
            3 => ''
            )
        ),
    'URLS' => array(
        1 => '',
        2 => '',
        3 => 'http://qt-project.org/doc/qt-5/qml-qtbluetooth-{FNAMEL}.html',
        4 => 'http://qt-project.org/doc/qt-5/qml-qtgraphicaleffects-{FNAMEL}.html',
        5 => 'http://qt-project.org/doc/qt-5/qml-qtaudioengine-{FNAMEL}.html',
        6 => 'http://qt-project.org/doc/qt-5/qml-qtmultimedia-{FNAMEL}.html',
        7 => 'http://qt-project.org/doc/qt-5/qml-qtnfc-{FNAMEL}.html',
        8 => 'http://qt-project.org/doc/qt-5/qml-qtpositioning-{FNAMEL}.html',
        9 => 'http://qt-project.org/doc/qt-5/qml-qtqml-models-{FNAMEL}.html',
        10 => 'http://qt-project.org/doc/qt-5/qml-qtqml-{FNAMEL}.html',
        11 => 'http://qt-project.org/doc/qt-5/qml-qt-labs-folderlistmodel-{FNAMEL}.html',
        12 => 'http://qt-project.org/doc/qt-5/qtquick-localstorage-qmlmodule.html',
        13 => 'http://qt-project.org/doc/qt-5/qml-qt-labs-settings-{FNAMEL}.html',
        14 => 'http://qt-project.org/doc/qt-5/qml-qtquick-window-{FNAMEL}.html',
        15 => 'http://qt-project.org/doc/qt-5/qml-qtquick-xmllistmodel-{FNAMEL}.html',
        16 => 'http://qt-project.org/doc/qt-5/qml-qtquick-particles-{FNAMEL}.html',
        17 => 'http://qt-project.org/doc/qt-5/qml-qttest-{FNAMEL}.html',
        18 => 'http://qt-project.org/doc/qt-5/qml-qtquick-{FNAMEL}.html',
        19 => 'http://qt-project.org/doc/qt-5/qml-qtquick-controls-{FNAMEL}.html',
        20 => 'http://qt-project.org/doc/qt-5/qml-qtquick-dialogs-{FNAMEL}.html',
        21 => 'http://qt-project.org/doc/qt-5/qml-qtquick-layouts-{FNAMEL}.html',
        22 => 'http://qt-project.org/doc/qt-5/qml-qtsensors-{FNAMEL}.html',
        23 => 'http://qt-project.org/doc/qt-5/qml-qtwinextras-{FNAMEL}.html',
        24 => 'http://qt-project.org/doc/qt-5/qml-qtwebkit-{FNAMEL}.html'
        ),
    'OOLANG' => true,
    'OBJECT_SPLITTERS' => array(
        1 => '.'
        ),
    'REGEXPS' => array(
        ),
    'STRICT_MODE_APPLIES' => GESHI_MAYBE,
    'SCRIPT_DELIMITERS' => array(
        0 => array(
            '<script type="text/javascript">' => '</script>'
            ),
        1 => array(
            '<script language="javascript">' => '</script>'
            )
        ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
        0 => true,
        1 => true
        )
);
