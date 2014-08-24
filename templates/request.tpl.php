<table style="float: left; display: inline-block; width: 45%;">
    <thead>
        <tr>
            <th>GET Key</th>
            <th>Value</th>
        </tr>
    </thead>
    
    <tbody>
        <?php
        if ($_GET)
        {
            foreach ($_GET as $key => $value)
                print('<tr><td>' .$key. '</td><td>' .$value. '</td></tr>');
        } else 
            print('<tr><td colspan="2">The query string is empty</td></tr>');
        ?>
    </tbody>
</table>

<table style="float: right; display: inline-block; width: 45%">
    <thead>
        <tr>
            <th>POST Key</th>
            <th>Value</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($_POST)
        {
            foreach ($_POST as $key => $value)
                $r .= '<tr><td>' .$key. '</td><td>' .$value. '</td></tr>';
        } else 
            $r .= '<tr><td colspan="2">No posted data</td></tr>';
        ?>
    </tbody>
</table>
