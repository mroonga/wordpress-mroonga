# -*- ruby -*-

text_domain = "mroonga"
translated_languages = [
  "ja",
]
mo_files = translated_languages.collect do |language|
  "languages/#{text_domain}-#{language}.mo"
end

php_files = Dir.glob("*.php")

release_files = []
release_files += mo_files
release_files += php_files
release_files += ["readme.txt"]

def detect_version
  mroonga_php = File.join(__dir__, "mroonga.php")
  File.open(mroonga_php) do |input|
    input.each_line do |line|
      case line.chomp
      when /\AVersion: (.+)\z/
        return $1
      end
    end
  end
  nil
end

version = ENV["VERSION"] || detect_version

pot_file = "languages/#{text_domain}.pot"

file pot_file => php_files do
  sh("xgettext",
     "--language", "php",
     "--keyword=__",
     "--output", pot_file,
     "--package-name", "Mroonga",
     "--package-version", version,
     "--msgid-bugs-address",
     "https://github.com/mroonga/wordpress-mroonga/issues/new",
     *php_files)
end

def newer?(path1, path2)
  File.exist?(path1) and
    File.exist?(path2) and
    File.mtime(path1) > File.mtime(path2)
end

task :force

edit_po_files = []
translated_languages.each do |language|
  base_name = "languages/#{text_domain}-#{language}"
  po_file = "#{base_name}.po"
  edit_po_file = "#{base_name}.edit.po"
  timestamp_file = "#{base_name}.timestamp"
  edit_po_files << edit_po_file

  po_file_is_updated = newer?(po_file, timestamp_file)

  po_file_dependencies = [edit_po_file]
  edit_po_file_dependencies = [pot_file]
  if po_file_is_updated
    edit_po_file_dependencies << :force
  end
  file edit_po_file => edit_po_file_dependencies do
    if po_file_is_updated
      rm_f(edit_po_file)
    end

    unless File.exist?(edit_po_file)
      if File.exist?(po_file)
        cp(po_file, edit_po_file)
      else
        sh("msginit",
           "--input", pot_file,
           "--output-file", edit_po_file,
           "--locale", language,
           "--no-translator")
      end
    end

    sh("msgmerge",
       "--output-file", edit_po_file,
       "--sort-by-file",
       edit_po_file,
       pot_file)
  end

  file po_file => po_file_dependencies do
    sh("msgcat",
       "--output-file", po_file,
       "--no-location",
       "--sort-by-file",
       edit_po_file)
    po_content = File.read(po_file)
    File.open(po_file, "w") do |output|
      in_header = true
      po_content.each_line do |line|
        if in_header
          case line.chomp
          when ""
            in_header = false
            output.print(line)
          when /\A"POT-Creation-Date:/
          when /\A"PO-Revision-Date:/
          else
            output.print(line)
          end
        else
          output.print(line)
        end
      end
    end
    touch(timestamp_file)
  end
end

rule ".mo" => ".po" do |task|
  sh("msgfmt",
     "--output-file", task.name,
     task.source)
end

desc "Update translation"
task :translate => (edit_po_files + mo_files)


release_repository = "../wordpress-mroonga.release"

directory release_repository do
  sh("svn", "co",
     "https://plugins.svn.wordpress.org/mroonga",
     release_repository)
end

desc "Publish #{version}"
task :publish => [release_repository, :translate] do
  trunk = File.join(release_repository, "trunk")
  tag = File.join(release_repository, "tags", version)
  # TODO: Removed files
  release_files.each do |file|
    dest_file = File.join(trunk, file)
    dest_directory = File.dirname(dest_file)
    unless File.exist?(dest_directory)
      mkdir_p(dest_directory)
      sh("svn", "add", dest_directory)
    end
    dest_file_exist = File.exist?(dest_file)
    cp(file, dest_file)
    sh("svn", "add", dest_file) unless dest_file_exist
  end
  sh("svn", "ci",
     "--message", "Import #{version}",
     trunk)
  sh("svn", "cp", trunk, tag)
end

desc "Tag #{version}"
task :tag do
  sh("git", "tag", "-a", version, "-m", "#{version} has been released!!!")
  sh("git", "push", "--tags")
end

desc "Release #{version}"
task :release => [:publish, :tag]
