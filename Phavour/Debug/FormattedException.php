<?php
/**
 * Phavour PHP Framework Library
 *
 * @author      Phavour Project
 * @copyright   2013-2014 Phavour Project
 * @link        http://phavour-project.com
 * @license     http://phavour-project.com/license
 * @since       1.0.0
 * @package     Phavour
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace Phavour\Debug;

/**
 * FormattedException
 */
class FormattedException
{
    /**
     * Format an exception, and show it.
     * @param \Exception $e
     * @param string $title (optional) default 'Application Error'
     * @codeCoverageIgnore
     */
    public static function display(\Exception $e, $title = 'Application Error')
    {
        @ob_get_clean();
        echo '
            <html>
                <head>
                    <title>' . $title . '</title>
                    <style>
                        body{margin:0;padding:30px;font:14px/1.5 Helvetica,Arial,Verdana,sans-serif;}
                        span{line-height:30px;}
                        h1{margin:0 0 30px;font-size:40px;font-weight:normal;line-height:54px;border-bottom: 2px solid #CCCCCC;}
                        h2{margin:0 0 20px;font-size:30px;font-weight:normal;line-height:50px;border-bottom: 1px solid #CCCCCC;}
                        h3,h4{margin:0 0 20px;font-size:20px;font-weight:normal;line-height:40px;border-bottom: 1px solid #CCCCCC;}
                        a{color:#428bca;text-decoration:underline;}
                        a:hover{color:#2a6496;}
                        div.file-preview{display:block;height:300px;overflow-x:auto;}
                        table{min-width:100%;line-height:30px;}
                        td.cell-left{padding-right:10px;text-align:right;border-right:1px solid #CCCCCC;}
                        td.cell-right{padding-left:10px;text-align:left;}
                        td.width50{width:50px;}
                        td.width15p{width:15%;}
                        .highlight{background:yellow;}
                        td.code-case{font-family:monospace !important;}
                    </style>
                </head>
                <body>
                    <h1>' . $title . '</h1>
                    <h2><strong>Exception: </strong><br /><code>' . get_class($e) . '</code></h2>
                    <p>
                        <strong>Line: </strong>' . $e->getLine() . '<br />
                        <strong>File: </strong>' . $e->getFile() . '<br />
                        <strong>Code: </strong>' . $e->getCode() . '
                    </p>
                    <h3>Message</h3>
                    <p>
                        <strong>' . $e->getMessage() . '</strong><br />
                    </p>
                    <h3>Additional Information:</h3>
                    <p>
                        ' . self::getMoreInformationFromException($e) . '
                    </p>
                    <h3>Trace</h3>
                    <p>
                        <pre><code>' . $e->getTraceAsString() . '</code></pre><br />
                    </p>
                    '.
                    self::getSnippetFromFile($e->getFile(), $e->getLine())
                    .'
                </body>
            </html>
        ';
        return;
    }

    /**
     * Get a snippet of the file
     * @param string $file
     * @param integer $line
     * @return string
     * @codeCoverageIgnore
     */
    protected static function getSnippetFromFile($file, $line)
    {
        $defaultSnippet = '<code><strong>Snippet not available</strong></code>';
        if (file_exists($file)) {
            $start = $line;
            if ($start > 21) {
                $start = $line - 20;
            }
            $end = $start + 40;
            $highlight = highlight_file($file, true);
            if (!$highlight) {
                return $defaultSnippet;
            }
            $highlight = str_replace(array('<code>', '</code>'), array('',''), $highlight);
            $pieces = explode('<br />', $highlight);
            if (!$pieces) {
                return $defaultSnippet;
            }
            $i = 0;
            $str = '<h4><strong>File Preview: </strong>' . $file . '</h4>';
            $str .= '<div class="file-preview">';
            $str .= '<table><tr><td valign="top" class="cell-left width50">';
            for ($l=0;$l<count($pieces)+1;$l++){
                $str .= '' . ($l + 1) . '<br />';
            }
            $str .= '</td><td valign="top" class="cell-right code-case">';
            foreach ($pieces as $piece) {
                $i++;
                if ($i == $line) {
                    $str .= '<div class="highlight">' . $piece . '</div>';
                } else {
                    $str .= $piece;
                }
                $str .= '<br />';
            }
            $str .= '</td></tr></table>';
            $str .= '</div>';
            return $str;
        }

        return $defaultSnippet;
    }

    /**
     * Get further information from Exception, if it has the property 'additionalData'
     * @param \Exception $e
     * @return string
     * @codeCoverageIgnore
     */
    public static function getMoreInformationFromException(\Exception $e)
    {
        $default = 'No further information available.';
        if (!property_exists($e, 'additionalData')) {
            return $default;
        }
        if (!is_array($e->additionalData)) {
            return $default;
        }
        if (empty($e->additionalData)) {
            return $default;
        }
        $data = '<table>';
        foreach ($e->additionalData as $k => $v) {
            if (is_int($k) || is_string($k)) {
                $data .= '<tr>';
                $data .= '<td valign="top" class="cell-left width15p"><strong>' . $k . '</strong></td>';
                $data .= '<td valign="top" class="cell-right">';
                if (is_object($v)) {
                    $data .= '<xmp>' . serialize($v) . '</xmp>';
                } elseif (is_array($v)) {
                    $data .= '<xmp>' . json_encode($v) . '</xmp>';
                } else {
                    $data .= '<xmp>' . $v . '</xmp>';
                }
                $data .= '</td>';
                $data .= '</tr>';
            }
        }
        $data .= '</table>';

        return $data;
    }
}