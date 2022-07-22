# parserOfSonarr

从 [Sonarr][sonarr_url] 拆下来的解析器

## demo

```shell
$ main.exe "[xx字幕组][彻夜之歌 / Yofukashi no Uta][03][简日双语][1080P][WEBrip][MP4]"

Yofukashi no Uta - 003 WEBRip-1080p v1

ParsedEpisodeInfo:
ReleaseTitle = [xx字幕组] Yofukashi no Uta - 03 [简日双语][1080P][WEBrip][MP4]
SeriesTitle = Yofukashi no Uta
SeriesTitleInfo =
  Title = Yofukashi no Uta
  TitleWithoutYear = Yofukashi no Uta
  Year = 0
  AllTitles =
Quality =
  Quality =
    Id = 15
    Name = WEBRip-1080p
    Source =
    Resolution = 1080
  Revision =
    Version = 1
    Real = 0
    IsRepack = False
  SourceDetectionSource =
  ResolutionDetectionSource =
  RevisionDetectionSource =
SeasonNumber = 0
EpisodeNumbers = 
AbsoluteEpisodeNumbers = 3
SpecialAbsoluteEpisodeNumbers = 
AirDate =
Language =
  Id = 10
  Name = Chinese
FullSeason = False
IsPartialSeason = False
IsMultiSeason = False
IsSeasonExtra = False
Special = False
ReleaseGroup = xx字幕组
ReleaseHash = 
SeasonPart = 0
ReleaseTokens =  [简日双语][1080P][WEBrip][MP4]
DailyPart =
IsDaily = False
IsAbsoluteNumbering = True
IsPossibleSpecialEpisode = False
IsPossibleSceneSeasonSpecial = False
```


## Licenses
同 [Sonarr][sonarr_url]

[sonarr_url]: https://github.com/Sonarr/Sonarr/