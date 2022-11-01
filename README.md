<p align="center"><h3>Auphonic integration</h3></p>

##
This code is built on the basis of a Podcast structure. The simple database structure is as follows:

Tables:
`users`

`podcasts`

`episodes`

Relations:
`user has many podcasts`

`podcast has many episodes`

`episode belongs to podcast`

##
This integrates Auphonic audio optimization. Auphonic optmizes audio using various algorithms and settings, which are described and configured using the `App\Helpers\AuphonicSettings` class. This class can then be passed along to the `App\Helpers\Auphonic` class along with an Episode model.

It starts of at the Route `/api/episodes/{episode}/optimize-audio` And follows the standard laravel Controller route to dispatch a job. The code should explain itself, but if you have any questions please ask.
