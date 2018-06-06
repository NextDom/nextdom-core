<?php

/* This file is part of NextDom.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom. If not, see <http://www.gnu.org/licenses/>.
 */
?>
<header class="navbar navbar-fixed-top navbar-default reportModeHidden">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="<?php echo $homeLink; ?>">
                <img src="core/img/logo-nextdom-grand-nom-couleur.svg" height="30"/>
            </a>
            <button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">{{Toggle navigation}}</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <nav class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li><a href="index.php?v=d&p=system&rescue=1"><i class="fa fa-terminal"></i> {{Système}}</a>
                </li>
                <li><a href="index.php?v=d&p=database&rescue=1"><i class="fa fa-database"></i> {{Database}}</a>
                </li>
                <li><a href="index.php?v=d&p=editor&rescue=1"><i class="fa fa-indent"></i> {{Editeur}}</a></li>
                <li><a href="index.php?v=d&p=custom&rescue=1"><i class="fa fa-pencil-square-o"></i>
                        {{Personnalisation}}</a></li>
                <li><a href="index.php?v=d&p=backup&rescue=1"><i class="fa fa-floppy-o"></i> {{Sauvegarde}}</a>
                </li>
                <li><a href="index.php?v=d&p=cron&rescue=1"><i class="fa fa-tasks"></i> {{Moteur de tâches}}</a>
                </li>
                <li><a href="index.php?v=d&p=log&rescue=1"><i class="fa fa-file-o"></i> {{Log}}</a></li>
            </ul>

        </nav>
    </div>
</header>

