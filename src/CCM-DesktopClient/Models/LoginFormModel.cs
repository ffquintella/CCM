using System.ComponentModel.DataAnnotations;

namespace BlazorDesktopClient.Models
{
    public class LoginFormModel
    {
        [Required]
        [StringLength(30, MinimumLength = 5, ErrorMessage = "Login is too long or too short")]
        public string Login { get; set; }
        
        [Required]
        public string Password { get; set; }
        
        public bool RememberMe { get; set; }
    }
}