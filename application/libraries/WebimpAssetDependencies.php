<?php
class WebimpAssetDependencies
{
    // an array of registered handle objects
    public $registered = [];

    // an array of queued WebimpScript handle objects
    public $queue = [];

    // an array of WebimpScript handle objects to queue
    public $to_do = [];

    // an array of WebimpScript handle objects already queued
    public $done = [];

    // an array of additional arguments passed when a handle is registered
    public $args = [];

    // an array of handle groups to enqueue
    public $groups = [];

    // a handle group to enqueue
    public $group = 0;




    /**
     * Processes the items and dependencies.
     *
     * Processes the items passed to it or the queue, and their dependencies.
     *
     * @param  mixed  $handles Optional. Items to be processed: Process queue (false),
     *                         process item (string), process items (array of strings).
     * @param  mixed  $group   Group level: level (int), no groups (false).
     *
     * @return array           Handles of items that have been processed.
     */
    public function do_items($handles = false, $group = false)
    {
        $handles = ($handles === false) ? $this->queue : (array) $handles;

        $this->all_deps($handles);

        foreach ($this->to_do as $key => $handle) {
            if (!in_array($handle, $this->done, true) &&
                isset($this->registered[$handle])
            ) {
                if ($this->do_item($handle, $group)) {
                    $this->done[] = $handle;
                }

                unset($this->to_do[$key]);
            }
        }

        return $this->done;
    }




    /**
     * Processes a dependency.
     *
     * @param  string  $handle  Name of th item. SHould be unique.
     *
     * @return bool             True on success, false if not set.
     */
    public function do_item($handle)
    {
        return isset($this->registered[$handle]);
    }




    /**
     * Determines dependencies.
     *
     * Recursively builds an array of items to process taking
     * dependencies into account. Does NOT catch infinite loops.
     *
     * @param  mixed      $handles    Item handle and argument (string) or item handles and arguments
     *                                (array of string).
     * @param  boolean    $recursion  Internal flag that function to call itself.
     * @param  int|false  $group      Group level: (int) level, (false) no groups.
     *
     * @return bool                   True on success, false on failure.
     */
    public function all_deps($handles, $recursion = false, $group = false)
    {
        if (!$handles = (array) $handles) {
            return false;
        }

        foreach ($handles as $handle) {
            $handle_parts = explode('?', $handle);
            $handle = $handle_parts[0];
            $queued = in_array($handle, $this->to_do, true);

            // already done
            if (in_array($handle, $this->done, true)) {
                continue;
            }

            $moved = $this->set_group($handle, $recursion, $group);
            $new_group = $this->groups[$handle];

            // already queued and in the right group
            if ($queued && !$moved) {
                continue;
            }

            $keep_going = true;
            if (!isset($this->registered[$handle])) {
                // item doesn't exist
                $keep_going = false;
            } elseif ($this->registered[$handle]->deps &&
                array_diff($this->registered[$handle]->deps, array_keys($this->registered))
            ) {
                // item requires dependencies that don't exist
                $keep_going = false;
            } elseif ($this->registered[$handle]->deps &&
                !$this->all_deps($this->registered[$handle]->deps, true, $new_group)
            ) {
                // item requires dependencies that don't exist
                $keep_going = false;
            }

            if (!$keep_going) {
                // either item or its dependencies don't exist
                if ($recursion) {
                    // abort this branch
                    return false;
                } else {
                    // we're at the top level; move on to the next one
                    continue;
                }
            }

            if ($queued) {
                // already grabbed it and its dependencies
                continue;
            }

            if (isset($handle_parts[1])) {
                $this->args[$handle] = $handle_parts[1];
            }

            $this->to_do[] = $handle;
        }

        return true;
    }




    /**
     * Register an item.
     *
     * @param string   $handle  Name of the item. Should be unique.
     * @param string   $src     Full URL of the item, or path of the item relative to the WordPress root directory.
     * @param array    $deps    Optional. An array of registered item handles this item depends on. Default empty array.
     * @param boolean  $ver     Optional. String specifying item version number, if it has one, which is added to the URL
     *                          as a query string for cache busting purposes. If version is set to false, a version
     *                          number is automatically added equal to current installed WordPress version.
     *                          If set to null, no version is added.
     * @param mixed    $args    Optional. Custom property of the item. NOT the class property $args. Examples: $media, $in_footer.
     *
     * @return bool Whether the item has been registered. True on success, false on failure.
     */
    public function add($handle, $src, $deps = [], $ver = false, $args = null)
    {
        if (isset($this->registered[$handle])) {
            return false;
        }

        $this->registered[$handle] = new WebimpAssetDependency($handle, $src, $deps, $ver, $args);

        return true;
    }




    /**
     * Add extra item data.
     *
     * Adds data to a registered item.
     *
     * @param string  $handle  Name of the item. Should be unique.
     * @param string  $key     The data key.
     * @param mixed   $value   The data value.
     *
     * @return bool True on success, false on failure.
     */
    public function add_data($handle, $key, $value)
    {
        if (!isset($this->registered[$handle])) {
            return false;
        }

        return $this->registered[$handle]->add_data($key, $value);
    }




    /**
     * Get extra item data.
     *
     * Gets data associated with a registered item.
     *
     * @param  string  $handle  Name of the item. Should be unique.
     * @param  string  $key     The data key.
     *
     * @return mixed            Extra item data (string), false otherwise.
     */
    public function get_data($handle, $key)
    {
        if (!isset($this->registered[$handle])) {
            return false;
        }

        if (!isset($this->registered[$handle]->extra[$key])) {
            return false;
        }

        return $this->registered[$handle]->extra[$key];
    }




    /**
     * Queue an item or items.
     *
     * Decodes handles and arguments, then queues handles and stores
     * arguments in the class property $args. For example in extending
     * classes,  $args is appended to the item url as a query string.
     *
     * Note: $args is NOT the $args property of the items in the $registered array.
     *
     * @param  mixed  $handles  Item handle and argument (string) or item handles
     *                          and arguments (array of strings).
     *
     * @return void
     */
    public function enqueue($handles)
    {
        foreach ((array) $handles as $handle) {
            $handle = explode('?', $handle);

            if (!in_array($handle[0], $this->queue) &&
                isset($this->registered[$handle[0]])
            ) {
                $this->queue[] = $handle[0];

                if (isset($handle[1])) {
                    $this->args[$handle[0]] = $handle[1];
                }
            }
        }
    }




    /**
     * Set item group, unless already in a lower group.
     *
     * @param string   $handle     [description]
     * @param boolean  $recursion  Internal flag where calling function was called recursively.
     * @param mixed    $group      Group level.
     *
     * @return boolean             Not already in the group or a lower group.
     */
    public function set_group($handle, $recursion, $group)
    {
        $group = (int) $group;

        if (isset($this->groups[$handle]) &&
            $this->groups[$handle] <= $group
        ) {
            return false;
        }

        $this->groups[$handle] = $group;

        return true;
    }
}
