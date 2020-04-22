using System;
using System.Data;
using System.IO;
using CCM_API.Security;
using Serilog;

namespace CCM_API
{
    public class FileManager
    {

        public string baseFileDirectory = "fileStorage";
        
        protected readonly ILogger logger = Log.Logger;
        
        public FileManager()
        {
            
        }
        
        public Tuple<string, string> CreateNewApiFile(string extension)
        {
            var basePath = Path.Combine(  
                Directory.GetCurrentDirectory(), baseFileDirectory); 
            try
            {
                Directory.CreateDirectory(basePath); // fileStorage dir

                var year = DateTime.Now.Year.ToString();
                var month = DateTime.Now.Month.ToString();
                
                var datePath = Path.Combine(  
                    basePath, year, month); 
                
                Directory.CreateDirectory(datePath);
                var random = new Random();
                var fileName = HashGenerator.GetMd5(string.Format("{0}-{1}-{2}-{3}-{4}",
                    DateTime.Now.Date,
                    DateTime.Now.Hour,
                    DateTime.Now.Minute,
                    DateTime.Now.Second, 
                    random.Next(1000,9999)))+ "." + extension;
                
                File.Create(datePath + "/" + fileName).Dispose();
                return new Tuple<string, string>(datePath + "/" + fileName, string.Format("/{0}/{1}/{2}",year, month, fileName));

            }
            catch (Exception ex)
            {
                logger.Error("Error in fileStorage:" + ex.Message);
                return null;
            }
        }
    }
}