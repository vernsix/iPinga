<?php
namespace iPinga;

class menu
{
    /**
     * @var array
     */
    private $links;

    /**
     * @var string
     */
    public $id;


    public function __construct($id = 'accordion')
    {
        $this->links = array();
        $this->id = $id;
    }

    /**
     * @param string $tab
     * @param string $name
     * @param string $url
     * @param string $target
     */
    public function addItem( $tab, $name, $url, $target='' )
    {
        if (isset($this->links[$tab])==false) {
            $this->links[$tab] = array();
        }
        $this->links[$tab][] = new \iPinga\menuItem($name,$url,$target);
    }

    /**
     * @return string
     */
    public function asHtml()
    {
        $r = "<!-- Start of accordion menu -->\r\n";
        $r .= '<ul id="'. $this->id . '">' . "\r\n";
        foreach ($this->links as $tab => $menuItems) {
            $r .= '<li>'. "\r\n";
            $r .= '   <h3><a>' . $tab . '</a></h3>'. "\r\n";
            $r .= '   <ul>'. "\r\n";
            foreach( $menuItems as $menuItem )  {
                $r .= '         <li><a href="'. $menuItem->url. '"';
                if (!empty($menuItem->target))
                {
                    $r .= ' target="'. $menuItem->target . '"';
                }
                $r .= '>' . $menuItem->name . '</a></li>'. "\r\n";
            }
            $r .= '   </ul>'. "\r\n";
            $r .= '</li>'. "\r\n";
        }
        $r .= '</ul>';
        $r .= '<!-- End of accordion menu -->'. "\r\n";
        return $r;
    }

}
