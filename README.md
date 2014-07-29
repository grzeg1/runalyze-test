runalyze-test
=============

This is the excellent runalyze.de webapp with some of my changes.
Thanks go to the authors of Runalyze.

=============

Use the conf.sql to init configuration settings for best experience as not every layout is supported yet.

Changes include:

- TRIMP calculation based on HR zones
- ATL/CTL using TSS model
- ATL/CTL in form graph using Banister model
- Best segments (Cooper, 1k, 5k, 21.1k, 42.2k) for each workout
- Colours and mini-charts in HR and pace zones in training view
- Some changes to the training view graph
  - thicker lines
  - colors for hr zones
  - better scaling
  - tooltip includes time
  - fixed selection stats calculations
  - pace shown as speed (5km/min 2x faster than 10km/min), smoothing
  - map cursor follows chart cursor
- Some changes to map
  - disabled wheel scroll in main view
  - fullscreen with charts
  - show cursor following chart cursor
  - thicker route line
- Better scaled lap km graph, tag for each km, average
- Form chart changes
  - using Banister model
  - colours
  - show TRIMP and VDOT for trainings
  - show TSB(shape)
