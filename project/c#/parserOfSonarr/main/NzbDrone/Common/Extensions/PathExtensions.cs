using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;

namespace NzbDrone.Common.Extensions
{   
    
    public static class PathExtensions
    {
        public static string GetPathExtension(this string path)
        {
            var idx = path.LastIndexOf('.');
            if (idx == -1 || idx == path.Length - 1)
            {
                return string.Empty;
            }

            return path.Substring(idx);
        }
        
        public static bool ContainsInvalidPathChars(this string text)
        {
            return text.IndexOfAny(Path.GetInvalidPathChars()) >= 0;
        }

    }
}
