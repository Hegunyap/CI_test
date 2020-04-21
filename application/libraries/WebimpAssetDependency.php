<?php
class WebimpAssetDependency
{
    // the handle name
    public $handle;

    // the handle srouce
    public $src;

    // an array of handle dependencies
    public $deps = [];

    // the handle version
    public $ver = false;

    // additional arguments for the handle
    public $args = null;

    // extra data to supply to the handle
    public $extra = [];




    /**
     * Setup script dependency
     */
    public function __construct()
    {
        @list($this->handle, $this->src, $this->deps, $this->ver, $this->args) = func_get_args();

        if (!is_array($this->deps)) {
            $this->deps = [];
        }
    }




    /**
     * Add handle data
     *
     * @param string  $name  The data key to add.
     * @param mixed   $data  The data value to add.
     *
     * @return bool          False if not scalar, true otherwise.
     */
    public function add_data($name, $data)
    {
        if (!is_scalar($name)) {
            return false;
        }

        $this->extra[$name] = $data;

        return true;
    }
}
